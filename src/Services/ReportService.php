<?php

namespace ME\Retail\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ME\Retail\Enums\CashRegisterSessionStatus;
use ME\Retail\Enums\EmiContractStatus;
use ME\Retail\Enums\EmiInstallmentStatus;
use ME\Retail\Enums\LoyaltyTransactionType;
use ME\Retail\Models\Attribute;
use ME\Retail\Models\Batch;
use ME\Retail\Models\Brand;
use ME\Retail\Models\CashRegister;
use ME\Retail\Models\CashRegisterSession;
use ME\Retail\Models\Category;
use ME\Retail\Models\Customer;
use ME\Retail\Models\CustomerLevel;
use ME\Retail\Models\DiscountRule;
use ME\Retail\Models\EmiBank;
use ME\Retail\Models\EmiContract;
use ME\Retail\Models\EmiInstallment;
use ME\Retail\Models\EmiPlan;
use ME\Retail\Models\Expense;
use ME\Retail\Models\ExpenseCategory;
use ME\Retail\Models\LoyaltyTransaction;
use ME\Retail\Models\Membership;
use ME\Retail\Models\Product;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\PurchaseReturn;
use ME\Retail\Models\Sale;
use ME\Retail\Models\SaleItem;
use ME\Retail\Models\SalePayment;
use ME\Retail\Models\SaleReturn;
use ME\Retail\Models\StockAdjustment;
use ME\Retail\Models\StockLedger;
use ME\Retail\Models\StockTransfer;
use ME\Retail\Models\Supplier;
use ME\Retail\Models\Unit;
use ME\Retail\Models\Warehouse;

/**
 * Report architecture (plan §45): every report is a query built here, never
 * inline in a controller, so the same aggregation logic can be reused across
 * the report pages and the dashboard widgets without duplication.
 */
class ReportService
{
    /**
     * @param string $groupBy one of: day, product, category, customer, cashier, store, payment_method
     */
    public function salesReport(string $from, string $to, string $groupBy = 'day', ?int $storeId = null): Collection
    {
        $query = SaleItem::query()
            ->join('retail_sales', 'retail_sales.id', '=', 'retail_sale_items.sale_id')
            ->whereBetween('retail_sales.sale_date', [$from, $to]);

        if ($storeId) {
            $query->where('retail_sales.store_id', $storeId);
        }

        return match ($groupBy) {
            'product' => $query->join('retail_products', 'retail_products.id', '=', 'retail_sale_items.product_id')
                ->selectRaw('retail_products.id, retail_products.name as label, SUM(retail_sale_items.quantity_base) as quantity, SUM(retail_sale_items.total) as total')
                ->groupBy('retail_products.id', 'retail_products.name')
                ->orderByDesc('total')
                ->get(),

            'category' => $query->join('retail_products', 'retail_products.id', '=', 'retail_sale_items.product_id')
                ->join('retail_categories', 'retail_categories.id', '=', 'retail_products.category_id')
                ->selectRaw('retail_categories.id, retail_categories.name as label, SUM(retail_sale_items.quantity_base) as quantity, SUM(retail_sale_items.total) as total')
                ->groupBy('retail_categories.id', 'retail_categories.name')
                ->orderByDesc('total')
                ->get(),

            'customer' => $query->leftJoin('retail_customers', 'retail_customers.id', '=', 'retail_sales.customer_id')
                ->selectRaw('COALESCE(retail_customers.id, 0) as id, COALESCE(retail_customers.name, "Walk-in Customer") as label, SUM(retail_sale_items.total) as total')
                ->groupBy('retail_customers.id', 'retail_customers.name')
                ->orderByDesc('total')
                ->get(),

            'cashier' => $query->leftJoin('users', 'users.id', '=', 'retail_sales.user_id')
                ->selectRaw('COALESCE(users.id, 0) as id, COALESCE(users.name, "N/A") as label, SUM(retail_sale_items.total) as total')
                ->groupBy('users.id', 'users.name')
                ->orderByDesc('total')
                ->get(),

            'store' => $query->join('retail_stores', 'retail_stores.id', '=', 'retail_sales.store_id')
                ->selectRaw('retail_stores.id, retail_stores.name as label, SUM(retail_sale_items.total) as total')
                ->groupBy('retail_stores.id', 'retail_stores.name')
                ->orderByDesc('total')
                ->get(),

            'payment_method' => SalePayment::query()
                ->join('retail_sales', 'retail_sales.id', '=', 'retail_sale_payments.sale_id')
                ->whereBetween('retail_sales.sale_date', [$from, $to])
                ->when($storeId, fn ($q) => $q->where('retail_sales.store_id', $storeId))
                ->selectRaw('retail_sale_payments.payment_method as label, SUM(retail_sale_payments.amount) as total')
                ->groupBy('retail_sale_payments.payment_method')
                ->orderByDesc('total')
                ->get(),

            default => $query->selectRaw('retail_sales.sale_date as label, SUM(retail_sale_items.total) as total, COUNT(DISTINCT retail_sales.id) as invoice_count')
                ->groupBy('retail_sales.sale_date')
                ->orderBy('retail_sales.sale_date')
                ->get(),
        };
    }

    /**
     * @param string $groupBy one of: day, supplier, product
     */
    public function purchaseReport(string $from, string $to, string $groupBy = 'day'): Collection
    {
        $query = DB::table('retail_purchase_items')
            ->join('retail_purchases', 'retail_purchases.id', '=', 'retail_purchase_items.purchase_id')
            ->whereBetween('retail_purchases.purchase_date', [$from, $to]);

        return match ($groupBy) {
            'supplier' => $query->join('retail_suppliers', 'retail_suppliers.id', '=', 'retail_purchases.supplier_id')
                ->selectRaw('retail_suppliers.id, retail_suppliers.name as label, SUM(retail_purchase_items.total) as total')
                ->groupBy('retail_suppliers.id', 'retail_suppliers.name')
                ->orderByDesc('total')
                ->get(),

            'product' => $query->join('retail_products', 'retail_products.id', '=', 'retail_purchase_items.product_id')
                ->selectRaw('retail_products.id, retail_products.name as label, SUM(retail_purchase_items.quantity_base) as quantity, SUM(retail_purchase_items.total) as total')
                ->groupBy('retail_products.id', 'retail_products.name')
                ->orderByDesc('total')
                ->get(),

            default => $query->selectRaw('retail_purchases.purchase_date as label, SUM(retail_purchase_items.total) as total, COUNT(DISTINCT retail_purchases.id) as invoice_count')
                ->groupBy('retail_purchases.purchase_date')
                ->orderBy('retail_purchases.purchase_date')
                ->get(),
        };
    }

    /**
     * Revenue - COGS - Returns - Expenses, using each sale item's cost captured
     * at the time it was sold — never today's product cost (plan §46).
     */
    public function profitReport(string $from, string $to, ?int $storeId = null): array
    {
        $itemTotals = SaleItem::query()
            ->join('retail_sales', 'retail_sales.id', '=', 'retail_sale_items.sale_id')
            ->whereBetween('retail_sales.sale_date', [$from, $to])
            ->when($storeId, fn ($q) => $q->where('retail_sales.store_id', $storeId))
            ->selectRaw('SUM(retail_sale_items.total) as revenue, SUM(retail_sale_items.quantity_base * retail_sale_items.cost_price) as cogs')
            ->first();

        $revenue = (float) ($itemTotals->revenue ?? 0);
        $cogs    = (float) ($itemTotals->cogs ?? 0);

        $returns = SaleReturn::query()
            ->whereBetween('return_date', [$from, $to])
            ->when($storeId, fn ($q) => $q->whereHas('sale', fn ($s) => $s->where('store_id', $storeId)))
            ->sum('total_refund');

        $expenses = Expense::query()
            ->whereBetween('expense_date', [$from, $to])
            ->when($storeId, fn ($q) => $q->where('store_id', $storeId))
            ->sum('amount');

        $grossProfit = $revenue - $cogs;
        $netProfit   = $grossProfit - (float) $returns - (float) $expenses;

        return [
            'revenue'      => $revenue,
            'cogs'         => $cogs,
            'gross_profit' => $grossProfit,
            'returns'      => (float) $returns,
            'expenses'     => (float) $expenses,
            'net_profit'   => $netProfit,
        ];
    }

    public function stockValuation(): array
    {
        $row = Product::query()
            ->selectRaw('SUM(stock_quantity * purchase_price) as cost_value, SUM(stock_quantity * sale_price) as retail_value')
            ->first();

        return [
            'cost_value'   => (float) ($row->cost_value ?? 0),
            'retail_value' => (float) ($row->retail_value ?? 0),
        ];
    }

    public function lowStockProducts()
    {
        return Product::with(['category', 'unit'])
            ->where('status', true)
            ->where('stock_quantity', '>', 0)
            ->where('stock_quantity', '<=', config('retail.low_stock_threshold', 10))
            ->orderBy('stock_quantity')
            ->get();
    }

    public function outOfStockProducts()
    {
        return Product::with(['category', 'unit'])
            ->where('status', true)
            ->where('stock_quantity', '<=', 0)
            ->get();
    }

    public function expiringBatches(?int $days = null)
    {
        $days = $days ?? config('retail.expiry_warning_days', 30);

        return Batch::with(['product', 'warehouse'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy('expiry_date')
            ->get();
    }

    public function expiredBatches()
    {
        return Batch::with(['product', 'warehouse'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', now()->toDateString())
            ->orderBy('expiry_date')
            ->get();
    }

    public function loyaltySummary(string $from, string $to): array
    {
        $totals = LoyaltyTransaction::query()
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('type, SUM(points) as total')
            ->groupBy('type')
            ->pluck('total', 'type');

        return [
            'earned'   => (float) ($totals[LoyaltyTransactionType::Earn->value] ?? 0),
            'redeemed' => abs((float) ($totals[LoyaltyTransactionType::Redeem->value] ?? 0)),
            'reversed' => abs((float) ($totals[LoyaltyTransactionType::Reversal->value] ?? 0)),
            'liability' => (float) LoyaltyTransaction::sum('points'),
        ];
    }

    public function customerLevelBreakdown()
    {
        return CustomerLevel::withCount('customers')->orderBy('min_points')->get();
    }

    public function cashRegisterReport(string $from, string $to)
    {
        return CashRegisterSession::with('cashRegister')
            ->whereBetween('opened_at', [$from, $to])
            ->orderByDesc('opened_at')
            ->get();
    }

    public function currentCashPosition(): float
    {
        return (float) CashRegisterSession::where('status', CashRegisterSessionStatus::Open)
            ->get()
            ->sum(fn (CashRegisterSession $session) => $session->current_balance);
    }

    public function emiSummary(): array
    {
        return [
            'active_contracts'      => EmiContract::where('status', EmiContractStatus::Active)->count(),
            'completed_contracts'   => EmiContract::where('status', EmiContractStatus::Completed)->count(),
            'paid_installments'     => EmiInstallment::where('status', EmiInstallmentStatus::Paid)->count(),
            'pending_installments'  => EmiInstallment::whereIn('status', [EmiInstallmentStatus::Pending, EmiInstallmentStatus::Partial])->count(),
            'overdue_installments'  => EmiInstallment::where('status', EmiInstallmentStatus::Overdue)->count(),
            'overdue_amount'        => (float) EmiInstallment::where('status', EmiInstallmentStatus::Overdue)->sum('remaining_amount'),
        ];
    }

    public function overdueEmiInstallments()
    {
        return EmiInstallment::with(['contract.customer', 'contract.sale'])
            ->where('status', EmiInstallmentStatus::Overdue)
            ->orderBy('due_date')
            ->get();
    }

    /**
     * One row count per catalog/setup/trade module, so the dashboard can give
     * every sidebar menu something to show without a bespoke query per widget.
     */
    public function moduleCounts(): array
    {
        return [
            'categories'         => Category::count(),
            'brands'             => Brand::count(),
            'units'              => Unit::count(),
            'attributes'         => Attribute::count(),
            'warehouses'         => Warehouse::count(),
            'suppliers'          => Supplier::count(),
            'customers'          => Customer::count(),
            'purchases'          => Purchase::count(),
            'purchase_returns'   => PurchaseReturn::count(),
            'sales'              => Sale::count(),
            'sale_returns'       => SaleReturn::count(),
            'stock_transfers'    => StockTransfer::count(),
            'stock_adjustments'  => StockAdjustment::count(),
            'memberships'        => Membership::count(),
            'discount_rules'     => DiscountRule::where('status', true)->count(),
            'cash_registers'     => CashRegister::count(),
            'expense_categories' => ExpenseCategory::count(),
            'emi_banks'          => EmiBank::count(),
            'emi_plans'          => EmiPlan::count(),
        ];
    }

    public function recentStockMovements(int $limit = 8)
    {
        return StockLedger::with(['product', 'warehouse'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
