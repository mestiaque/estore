<?php

use Illuminate\Support\Facades\Route;
use ME\Http\Middleware\LocaleMiddleware;
use ME\Retail\Http\Controllers\AttributeController;
use ME\Retail\Http\Controllers\AttributeValueController;
use ME\Retail\Http\Controllers\BarcodeController;
use ME\Retail\Http\Controllers\BrandController;
use ME\Retail\Http\Controllers\BusinessTypePresetController;
use ME\Retail\Http\Controllers\CashRegisterController;
use ME\Retail\Http\Controllers\CashRegisterSessionController;
use ME\Retail\Http\Controllers\CategoryController;
use ME\Retail\Http\Controllers\CustomerLevelController;
use ME\Retail\Http\Controllers\DiscountRuleController;
use ME\Retail\Http\Controllers\EmiBankController;
use ME\Retail\Http\Controllers\EmiContractController;
use ME\Retail\Http\Controllers\EmiInstallmentController;
use ME\Retail\Http\Controllers\EmiPlanController;
use ME\Retail\Http\Controllers\ExpenseCategoryController;
use ME\Retail\Http\Controllers\ExpenseController;
use ME\Retail\Http\Controllers\HeldSaleController;
use ME\Retail\Http\Controllers\InvoiceController;
use ME\Retail\Http\Controllers\LoyaltyAdjustmentController;
use ME\Retail\Http\Controllers\MembershipController;
use ME\Retail\Http\Controllers\ProductController;
use ME\Retail\Http\Controllers\ProductVariantController;
use ME\Retail\Http\Controllers\PurchaseController;
use ME\Retail\Http\Controllers\PurchasePaymentController;
use ME\Retail\Http\Controllers\PurchaseReturnController;
use ME\Retail\Http\Controllers\ReportController;
use ME\Retail\Http\Controllers\RetailController;
use ME\Retail\Http\Controllers\RetailSettingController;
use ME\Retail\Http\Controllers\SaleController;
use ME\Retail\Http\Controllers\SaleReturnController;
use ME\Retail\Http\Controllers\StockAdjustmentController;
use ME\Retail\Http\Controllers\StockLedgerController;
use ME\Retail\Http\Controllers\StockTransferController;
use ME\Retail\Http\Controllers\StoreController;
use ME\Retail\Http\Controllers\SupplierController;
use ME\Retail\Http\Controllers\CustomerController;
use ME\Retail\Http\Controllers\UnitController;
use ME\Retail\Http\Controllers\WarehouseController;

Route::group(['prefix' => 'retail', 'as' => 'retail.', 'middleware' => ['web', 'auth', LocaleMiddleware::class, 'retail.feature']], function () {
    Route::get('/', [RetailController::class, 'index'])->name('index');

    Route::resource('stores', StoreController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('units', UnitController::class);
    Route::resource('products', ProductController::class);
    Route::post('products/{product}/barcode/generate', [BarcodeController::class, 'generate'])->name('products.barcode.generate');
    Route::post('products/barcode/bulk-generate', [BarcodeController::class, 'bulkGenerate'])->name('products.barcode.bulk-generate');
    Route::get('barcode-labels', [BarcodeController::class, 'labelForm'])->name('barcode-labels.form');
    Route::post('barcode-labels/print', [BarcodeController::class, 'labelPrint'])->name('barcode-labels.print');
    Route::get('barcode-lookup', [BarcodeController::class, 'lookup'])->name('barcode.lookup');

    Route::get('products/{product}/variants/attributes', [ProductVariantController::class, 'attributesForm'])->name('products.variants.attributes-form');
    Route::post('products/{product}/variants/attributes', [ProductVariantController::class, 'attributesStore'])->name('products.variants.attributes-store');
    Route::get('products/{product}/variants/generate', [ProductVariantController::class, 'generateForm'])->name('products.variants.generate-form');
    Route::post('products/{product}/variants/generate', [ProductVariantController::class, 'generate'])->name('products.variants.generate');

    Route::resource('attributes', AttributeController::class);
    Route::post('attributes/{attribute}/values', [AttributeValueController::class, 'store'])->name('attribute-values.store');
    Route::delete('attribute-values/{attributeValue}', [AttributeValueController::class, 'destroy'])->name('attribute-values.destroy');

    Route::resource('warehouses', WarehouseController::class);

    // Append-only history — no edit/update/destroy.
    Route::resource('stock-ledgers', StockLedgerController::class)->only(['index', 'show']);
    Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store', 'show']);

    // Stock transfers execute atomically on creation — no separate receive step, no edit/delete.
    Route::resource('stock-transfers', StockTransferController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('suppliers', SupplierController::class);

    // Purchases are immutable once recorded — corrections happen via Purchase Return, not edit/delete.
    Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('purchases/{purchase}/payments', [PurchasePaymentController::class, 'store'])->name('purchases.payments.store');
    Route::get('purchases/{purchase}/return', [PurchaseReturnController::class, 'create'])->name('purchases.returns.create');
    Route::post('purchases/{purchase}/return', [PurchaseReturnController::class, 'store'])->name('purchases.returns.store');
    Route::get('purchase-returns', [PurchaseReturnController::class, 'index'])->name('purchases.returns.index');
    Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchases.returns.show');
    Route::get('purchases/{purchase}/invoice', [InvoiceController::class, 'purchase'])->name('purchases.invoice');

    Route::resource('customers', CustomerController::class);

    // Parked POS carts — resumed on retail.sales.create?resume={id}.
    Route::post('sales/hold', [HeldSaleController::class, 'store'])->name('sales.hold');
    Route::delete('held-sales/{heldSale}', [HeldSaleController::class, 'destroy'])->name('held-sales.destroy');

    // Sales are immutable once completed — corrections happen via Sale Return, not edit/delete.
    Route::resource('sales', SaleController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('sales/{sale}/return', [SaleReturnController::class, 'create'])->name('sales.returns.create');
    Route::post('sales/{sale}/return', [SaleReturnController::class, 'store'])->name('sales.returns.store');
    Route::get('sale-returns', [SaleReturnController::class, 'index'])->name('sales.returns.index');
    Route::get('sale-returns/{saleReturn}', [SaleReturnController::class, 'show'])->name('sales.returns.show');
    Route::get('sales/{sale}/invoice/{type?}', [InvoiceController::class, 'sale'])->name('sales.invoice');
    Route::get('sales/{sale}/emi/create', [EmiContractController::class, 'create'])->name('emi-contracts.create');
    Route::post('sales/{sale}/emi', [EmiContractController::class, 'store'])->name('emi-contracts.store');

    Route::resource('customer-levels', CustomerLevelController::class);
    Route::resource('memberships', MembershipController::class);

    Route::get('loyalty/adjust', [LoyaltyAdjustmentController::class, 'create'])->name('loyalty.adjust.create');
    Route::post('loyalty/adjust', [LoyaltyAdjustmentController::class, 'store'])->name('loyalty.adjust.store');

    Route::resource('discount-rules', DiscountRuleController::class);

    Route::resource('cash-registers', CashRegisterController::class);
    Route::get('cash-registers/{cashRegister}/open', [CashRegisterSessionController::class, 'openForm'])->name('cash-registers.open-form');
    Route::post('cash-registers/{cashRegister}/open', [CashRegisterSessionController::class, 'open'])->name('cash-registers.open');
    Route::get('cash-register-sessions/{cashRegisterSession}', [CashRegisterSessionController::class, 'show'])->name('cash-register-sessions.show');
    Route::get('cash-register-sessions/{cashRegisterSession}/close', [CashRegisterSessionController::class, 'closeForm'])->name('cash-register-sessions.close-form');
    Route::post('cash-register-sessions/{cashRegisterSession}/close', [CashRegisterSessionController::class, 'close'])->name('cash-register-sessions.close');

    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'show']);

    Route::resource('emi-banks', EmiBankController::class);
    Route::resource('emi-plans', EmiPlanController::class);
    Route::resource('emi-contracts', EmiContractController::class)->only(['index', 'show']);
    Route::post('emi-installments/{emiInstallment}/pay', [EmiInstallmentController::class, 'pay'])->name('emi-installments.pay');

    Route::prefix('settings')->as('settings.')->group(function () {
        Route::get('loyalty', [RetailSettingController::class, 'edit'])->name('loyalty.edit');
        Route::put('loyalty', [RetailSettingController::class, 'update'])->name('loyalty.update');
        Route::get('modules', [RetailSettingController::class, 'modules'])->name('modules.edit');
        Route::put('modules', [RetailSettingController::class, 'updateModules'])->name('modules.update');
        Route::get('business-type', [RetailSettingController::class, 'businessType'])->name('business-type.edit');
        Route::put('business-type', [RetailSettingController::class, 'updateBusinessType'])->name('business-type.update');
        Route::get('general', [RetailSettingController::class, 'general'])->name('general.edit');
        Route::put('general', [RetailSettingController::class, 'updateGeneral'])->name('general.update');

        Route::get('business-types/create', [BusinessTypePresetController::class, 'create'])->name('business-types.create');
        Route::post('business-types', [BusinessTypePresetController::class, 'store'])->name('business-types.store');
        Route::post('business-types/reset', [BusinessTypePresetController::class, 'reset'])->name('business-types.reset');
        Route::get('business-types/{type}/edit', [BusinessTypePresetController::class, 'edit'])->name('business-types.edit');
        Route::put('business-types/{type}', [BusinessTypePresetController::class, 'update'])->name('business-types.update');
        Route::delete('business-types/{type}', [BusinessTypePresetController::class, 'destroy'])->name('business-types.destroy');
    });

    Route::prefix('reports')->as('reports.')->group(function () {
        Route::get('sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases');
        Route::get('profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('loyalty', [ReportController::class, 'loyalty'])->name('loyalty');
        Route::get('cash-register', [ReportController::class, 'cashRegister'])->name('cash-register');
        Route::get('emi', [ReportController::class, 'emi'])->name('emi');
    });
});
