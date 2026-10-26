<?php

namespace ME\Retail\Http\Controllers;

use Illuminate\Http\Request;
use ME\Http\Controllers\Controller;
use ME\Retail\Models\Store;
use ME\Retail\Services\ReportService;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reportService)
    {
        $this->middleware('authorization:retail_report.view');
    }

    public function sales(Request $request)
    {
        [$from, $to] = $this->range($request);
        $groupBy = $request->get('group_by', 'day');
        $storeId = $request->get('store_id');

        $rows    = $this->reportService->salesReport($from, $to, $groupBy, $storeId ?: null);
        $stores  = Store::where('status', true)->get();

        return view('retail::reports.sales', compact('rows', 'from', 'to', 'groupBy', 'stores', 'storeId'));
    }

    public function purchases(Request $request)
    {
        [$from, $to] = $this->range($request);
        $groupBy = $request->get('group_by', 'day');

        $rows = $this->reportService->purchaseReport($from, $to, $groupBy);

        return view('retail::reports.purchases', compact('rows', 'from', 'to', 'groupBy'));
    }

    public function profit(Request $request)
    {
        [$from, $to] = $this->range($request);
        $storeId = $request->get('store_id');

        $data   = $this->reportService->profitReport($from, $to, $storeId ?: null);
        $stores = Store::where('status', true)->get();

        return view('retail::reports.profit', compact('data', 'from', 'to', 'stores', 'storeId'));
    }

    public function inventory(Request $request)
    {
        $type = $request->get('type', 'low_stock');

        $rows = match ($type) {
            'out_of_stock' => $this->reportService->outOfStockProducts(),
            'expiring'     => $this->reportService->expiringBatches(),
            'expired'      => $this->reportService->expiredBatches(),
            default        => $this->reportService->lowStockProducts(),
        };

        $valuation = $this->reportService->stockValuation();

        return view('retail::reports.inventory', compact('rows', 'type', 'valuation'));
    }

    public function loyalty(Request $request)
    {
        [$from, $to] = $this->range($request);

        $summary = $this->reportService->loyaltySummary($from, $to);
        $levels  = $this->reportService->customerLevelBreakdown();

        return view('retail::reports.loyalty', compact('summary', 'levels', 'from', 'to'));
    }

    public function cashRegister(Request $request)
    {
        [$from, $to] = $this->range($request);

        $sessions = $this->reportService->cashRegisterReport($from, $to);

        return view('retail::reports.cash-register', compact('sessions', 'from', 'to'));
    }

    public function emi()
    {
        $summary = $this->reportService->emiSummary();
        $overdue = $this->reportService->overdueEmiInstallments();

        return view('retail::reports.emi', compact('summary', 'overdue'));
    }

    private function range(Request $request): array
    {
        return [
            $request->get('from', now()->startOfMonth()->toDateString()),
            $request->get('to', now()->toDateString()),
        ];
    }
}
