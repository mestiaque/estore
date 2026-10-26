<?php

namespace ME\Retail\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use ME\Retail\Enums\CashRegisterTransactionType;
use ME\Retail\Enums\PaymentStatus;
use ME\Retail\Enums\StockMovementType;
use ME\Retail\Enums\SupplierTransactionType;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Product;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\PurchaseItem;
use ME\Retail\Models\PurchasePayment;
use ME\Retail\Models\Supplier;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;

/**
 * Orchestrates the full purchase flow described by the master plan:
 * Supplier -> Purchase -> Purchase Items -> Stock Ledger -> Inventory ->
 * Supplier Balance -> Payment — all inside a single atomic transaction.
 */
class PurchaseService
{
    public function __construct(
        protected StockService $stockService,
        protected SupplierLedgerService $supplierLedgerService,
        protected BatchService $batchService,
        protected SerialService $serialService,
        protected CashRegisterService $cashRegisterService
    ) {
    }

    /**
     * @param array{
     *   supplier_id:int, store_id:int, warehouse_id:int, purchase_date:string,
     *   reference_number?:string, discount?:float, tax?:float, paid_amount?:float,
     *   payment_method?:string, notes?:string,
     *   items: array<int, array{product_id:int, unit_id:int, quantity:float, purchase_price:float, discount?:float, tax?:float, batch_number?:string, batch_barcode?:string, expiry_date?:string, serial_numbers?:string[], warranty_months?:int}>
     * } $data
     */
    public function create(array $data, ?int $userId): Purchase
    {
        return DB::transaction(function () use ($data, $userId) {
            $supplier  = Supplier::findOrFail($data['supplier_id']);
            $warehouse = Warehouse::findOrFail($data['warehouse_id']);

            $itemRows = [];
            $subtotal = 0.0;

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $unit    = Unit::findOrFail($item['unit_id']);

                if ($unit->id !== $product->unit_id && $unit->base_unit_id !== $product->unit_id) {
                    throw new InvalidArgumentException(
                        "Unit \"{$unit->name}\" is not a valid purchase unit for product \"{$product->name}\"."
                    );
                }

                $quantity     = (float) $item['quantity'];
                $factor       = (float) $unit->conversion_factor ?: 1.0;
                $quantityBase = $quantity * $factor;
                $costPerBase  = $factor > 0 ? (float) $item['purchase_price'] / $factor : (float) $item['purchase_price'];

                if ($product->track_type === 'batch' && empty($item['batch_number'])) {
                    throw new InvalidArgumentException("A batch number is required for product \"{$product->name}\".");
                }

                if ($product->track_type === 'batch' && $product->has_expiry && empty($item['expiry_date'])) {
                    throw new InvalidArgumentException("An expiry date is required for product \"{$product->name}\".");
                }

                if ($product->track_type === 'serial') {
                    $serialNumbers = array_values(array_filter(array_map('trim', $item['serial_numbers'] ?? [])));

                    if (abs($quantityBase - round($quantityBase)) > 0.0001 || count($serialNumbers) !== (int) round($quantityBase)) {
                        throw new InvalidArgumentException(
                            "Product \"{$product->name}\" is serial-tracked: provide exactly {$quantityBase} unique serial number(s)."
                        );
                    }
                }

                $discount = (float) ($item['discount'] ?? 0);
                $tax      = (float) ($item['tax'] ?? 0);
                $total    = ($quantity * (float) $item['purchase_price']) - $discount + $tax;
                $subtotal += $total;

                $itemRows[] = [
                    'product'        => $product,
                    'unit'           => $unit,
                    'quantity'       => $quantity,
                    'quantity_base'  => $quantityBase,
                    'purchase_price' => $item['purchase_price'],
                    'cost_per_base'  => $costPerBase,
                    'discount'       => $discount,
                    'tax'            => $tax,
                    'total'          => $total,
                    'batch_number'   => $item['batch_number'] ?? null,
                    'batch_barcode'  => $item['batch_barcode'] ?? null,
                    'expiry_date'    => $item['expiry_date'] ?? null,
                    'serial_numbers' => $serialNumbers ?? [],
                    'warranty_months' => !empty($item['warranty_months']) ? (int) $item['warranty_months'] : null,
                ];
            }

            $invoiceDiscount = (float) ($data['discount'] ?? 0);
            $invoiceTax      = (float) ($data['tax'] ?? 0);
            $grandTotal      = $subtotal - $invoiceDiscount + $invoiceTax;
            $paidAmount      = (float) ($data['paid_amount'] ?? 0);
            $dueAmount       = max($grandTotal - $paidAmount, 0);

            $purchase = Purchase::create([
                'supplier_id'      => $supplier->id,
                'store_id'         => $data['store_id'],
                'warehouse_id'     => $warehouse->id,
                'invoice_number'   => $this->generateInvoiceNumber(),
                'reference_number' => $data['reference_number'] ?? null,
                'purchase_date'    => $data['purchase_date'],
                'subtotal'         => $subtotal,
                'discount'         => $invoiceDiscount,
                'tax'              => $invoiceTax,
                'grand_total'      => $grandTotal,
                'paid_amount'      => $paidAmount,
                'due_amount'       => $dueAmount,
                'payment_status'   => PaymentStatus::fromAmounts($grandTotal, $paidAmount),
                'notes'            => $data['notes'] ?? null,
                'user_id'          => $userId,
            ]);

            foreach ($itemRows as $row) {
                $purchaseItem = PurchaseItem::create([
                    'purchase_id'     => $purchase->id,
                    'product_id'      => $row['product']->id,
                    'unit_id'         => $row['unit']->id,
                    'quantity'        => $row['quantity'],
                    'quantity_base'   => $row['quantity_base'],
                    'purchase_price'  => $row['purchase_price'],
                    'discount'        => $row['discount'],
                    'tax'             => $row['tax'],
                    'total'           => $row['total'],
                ]);

                $this->stockService->move(
                    product: $row['product'],
                    warehouse: $warehouse,
                    quantity: $row['quantity_base'],
                    type: StockMovementType::Purchase,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    userId: $userId,
                    notes: "Purchase {$purchase->invoice_number}"
                );

                if ($row['product']->track_type === 'batch') {
                    $batch = $this->batchService->findOrCreate(
                        $row['product'],
                        $warehouse,
                        $row['batch_number'],
                        $row['expiry_date'],
                        $row['cost_per_base'],
                        $row['batch_barcode']
                    );
                    $this->batchService->increment($batch, $row['quantity_base']);
                    $purchaseItem->update(['batch_id' => $batch->id]);
                }

                if ($row['product']->track_type === 'serial') {
                    $this->serialService->createMany(
                        $row['product'],
                        $warehouse,
                        $row['serial_numbers'],
                        $purchaseItem->id,
                        $row['cost_per_base'],
                        $data['purchase_date'],
                        $row['warranty_months']
                    );
                }

                // Forward-looking cost update only — historical purchases keep their own recorded price.
                $row['product']->update(['purchase_price' => $row['cost_per_base']]);
            }

            $this->supplierLedgerService->record(
                supplier: $supplier,
                type: SupplierTransactionType::Purchase,
                amount: $grandTotal,
                referenceType: Purchase::class,
                referenceId: $purchase->id,
                userId: $userId,
                notes: "Purchase {$purchase->invoice_number}"
            );

            if ($paidAmount > 0) {
                $method  = $data['payment_method'] ?? 'cash';
                $isCash  = $method === 'cash' && !empty($data['cash_register_session_id']);
                $session = $isCash ? CashRegisterSession::findOrFail($data['cash_register_session_id']) : null;

                PurchasePayment::create([
                    'purchase_id'                => $purchase->id,
                    'supplier_id'                => $supplier->id,
                    'amount'                     => $paidAmount,
                    'payment_method'             => $method,
                    'cash_register_session_id'   => $session?->id,
                    'user_id'                    => $userId,
                    'paid_at'                    => now(),
                ]);

                $this->supplierLedgerService->record(
                    supplier: $supplier,
                    type: SupplierTransactionType::Payment,
                    amount: -$paidAmount,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    userId: $userId,
                    notes: "Payment for purchase {$purchase->invoice_number}"
                );

                if ($session) {
                    $this->cashRegisterService->recordMovement(
                        session: $session,
                        type: CashRegisterTransactionType::PurchasePayment,
                        amount: -$paidAmount,
                        referenceType: Purchase::class,
                        referenceId: $purchase->id,
                        userId: $userId,
                        notes: "Payment for purchase {$purchase->invoice_number}"
                    );
                }
            }

            return $purchase;
        });
    }

    public function addPayment(Purchase $purchase, float $amount, string $method, ?string $reference, ?string $notes, ?int $userId, ?int $cashRegisterSessionId = null): PurchasePayment
    {
        return DB::transaction(function () use ($purchase, $amount, $method, $reference, $notes, $userId, $cashRegisterSessionId) {
            if ($amount <= 0) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            if ($amount > (float) $purchase->due_amount) {
                throw new InvalidArgumentException('Payment amount cannot exceed the remaining due amount.');
            }

            $session = ($method === 'cash' && $cashRegisterSessionId) ? CashRegisterSession::findOrFail($cashRegisterSessionId) : null;

            $payment = PurchasePayment::create([
                'purchase_id'                => $purchase->id,
                'supplier_id'                => $purchase->supplier_id,
                'amount'                     => $amount,
                'payment_method'             => $method,
                'cash_register_session_id'   => $session?->id,
                'reference'                  => $reference,
                'notes'                      => $notes,
                'user_id'                    => $userId,
                'paid_at'                    => now(),
            ]);

            $this->supplierLedgerService->record(
                supplier: $purchase->supplier,
                type: SupplierTransactionType::Payment,
                amount: -$amount,
                referenceType: Purchase::class,
                referenceId: $purchase->id,
                userId: $userId,
                notes: "Payment for purchase {$purchase->invoice_number}"
            );

            if ($session) {
                $this->cashRegisterService->recordMovement(
                    session: $session,
                    type: CashRegisterTransactionType::PurchasePayment,
                    amount: -$amount,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    userId: $userId,
                    notes: "Payment for purchase {$purchase->invoice_number}"
                );
            }

            $paidAmount = (float) $purchase->paid_amount + $amount;
            $dueAmount  = max((float) $purchase->grand_total - $paidAmount, 0);

            $purchase->update([
                'paid_amount'    => $paidAmount,
                'due_amount'     => $dueAmount,
                'payment_status' => PaymentStatus::fromAmounts((float) $purchase->grand_total, $paidAmount),
            ]);

            return $payment;
        });
    }

    private function generateInvoiceNumber(): string
    {
        // Locks the table's max id for the duration of the outer transaction so two
        // concurrent purchases cannot compute the same next number; the unique
        // constraint on invoice_number is the final safety net either way.
        $next = (int) (Purchase::query()->lockForUpdate()->max('id')) + 1;

        return 'PUR-' . now()->format('Y') . '-' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
    }
}
