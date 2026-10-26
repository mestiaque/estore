<?php
namespace ME\Retail\Http\Controllers;

use Illuminate\Support\Facades\DB;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Customer;
use ME\Retail\Models\Expense;
use ME\Retail\Models\Product;
use ME\Retail\Models\Purchase;
use ME\Retail\Models\Sale;
use ME\Retail\Models\SaleItem;
use ME\Retail\Models\Store;
use ME\Retail\Models\Supplier;
use ME\Retail\Services\ReportService;

class RetailController extends Controller
{
    public function index(ReportService $reportService)
    {
        $storeCount   = Store::count();
        $productCount = Product::count();

        $today = now()->toDateString();

        $todaysSales     = Sale::whereDate('sale_date', $today)->sum('grand_total');
        $todaysSaleCount = Sale::whereDate('sale_date', $today)->count();
        $yesterdaysSales = (float) Sale::whereDate('sale_date', now()->subDay()->toDateString())->sum('grand_total');
        $todaysPurchase = Purchase::whereDate('purchase_date', $today)->sum('grand_total');
        $todaysProfit   = $reportService->profitReport($today, $today)['net_profit'];
        // Single aggregate per side rather than looping every customer/supplier's due_balance accessor (avoids N+1).
        $customerDue = (float) Customer::sum('opening_balance') + (float) DB::table('retail_customer_transactions')->sum('amount');
        $supplierDue = (float) Supplier::sum('opening_balance') + (float) DB::table('retail_supplier_transactions')->sum('amount');
        $lowStockProducts = $reportService->lowStockProducts();
        $lowStockCount    = $lowStockProducts->count();
        $outOfStockCount  = $reportService->outOfStockProducts()->count();
        $expiringBatches  = $reportService->expiringBatches();
        $expiringCount    = $expiringBatches->count();
        $recentSales      = Sale::with('customer')->latest()->limit(6)->get();
        $cashPosition   = $reportService->currentCashPosition();
        $emiSummary      = $reportService->emiSummary();
        $emiOverdueCount = $emiSummary['overdue_installments'];

        $topProducts = SaleItem::query()
            ->join('retail_sales', 'retail_sales.id', '=', 'retail_sale_items.sale_id')
            ->join('retail_products', 'retail_products.id', '=', 'retail_sale_items.product_id')
            ->where('retail_sales.sale_date', '>=', now()->subDays(30)->toDateString())
            ->selectRaw('retail_products.name, SUM(retail_sale_items.quantity_base) as quantity, SUM(retail_sale_items.total) as total')
            ->groupBy('retail_products.id', 'retail_products.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $topCustomers = Sale::query()
            ->whereNotNull('customer_id')
            ->where('sale_date', '>=', now()->subDays(30)->toDateString())
            ->join('retail_customers', 'retail_customers.id', '=', 'retail_sales.customer_id')
            ->selectRaw('retail_customers.name, SUM(retail_sales.grand_total) as total')
            ->groupBy('retail_customers.id', 'retail_customers.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        $from = now()->subDays(29)->toDateString();
        $to   = now()->toDateString();

        // Reuse ReportService's grouped queries and fill in zero-days so the trend line has no gaps.
        $salesByDay    = $reportService->salesReport($from, $to, 'day')->pluck('total', 'label');
        $purchaseByDay = $reportService->purchaseReport($from, $to, 'day')->pluck('total', 'label');

        $trendLabels   = [];
        $trendSales    = [];
        $trendPurchase = [];
        for ($date = now()->subDays(29); $date->lte(now()); $date = $date->copy()->addDay()) {
            $key = $date->toDateString();
            $trendLabels[]   = $date->format('M d');
            $trendSales[]    = (float) ($salesByDay[$key] ?? 0);
            $trendPurchase[] = (float) ($purchaseByDay[$key] ?? 0);
        }

        $paymentMethodBreakdown = $reportService->salesReport($from, $to, 'payment_method');
        $categoryBreakdown      = $reportService->salesReport($from, $to, 'category')->take(6);
        $stockValuation         = $reportService->stockValuation();
        $customerLevels         = $reportService->customerLevelBreakdown();

        // Everything below feeds the "one tile per sidebar menu" widgets.
        $moduleCounts          = $reportService->moduleCounts();
        $recentStockMovements  = $reportService->recentStockMovements();
        $loyaltyThisMonth      = $reportService->loyaltySummary(now()->startOfMonth()->toDateString(), $to);
        $expensesThisMonth     = (float) Expense::whereBetween('expense_date', [now()->startOfMonth()->toDateString(), $to])->sum('amount');

        return view('retail::dashboard', compact(
            'storeCount', 'productCount', 'todaysSales', 'todaysPurchase', 'todaysProfit',
            'todaysSaleCount', 'yesterdaysSales', 'outOfStockCount', 'recentSales', 'lowStockProducts', 'expiringBatches',
            'customerDue', 'supplierDue', 'lowStockCount', 'expiringCount',
            'cashPosition', 'topProducts', 'topCustomers', 'emiOverdueCount', 'emiSummary',
            'trendLabels', 'trendSales', 'trendPurchase',
            'paymentMethodBreakdown', 'categoryBreakdown', 'stockValuation', 'customerLevels',
            'moduleCounts', 'recentStockMovements', 'loyaltyThisMonth', 'expensesThisMonth'
        ));
    }
}
