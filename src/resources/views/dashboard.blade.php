@extends('me::master')

@section('title', trans('Retail Dashboard'))

@section('content')
<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Today\'s Sales')</h6>
            <h3>{{ number_format($todaysSales, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Today\'s Purchase')</h6>
            <h3>{{ number_format($todaysPurchase, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Today\'s Profit')</h6>
            <h3 class="{{ $todaysProfit >= 0 ? 'text-success' : 'text-danger' }}">{{ number_format($todaysProfit, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Cash Position (open registers)')</h6>
            <h3>{{ number_format($cashPosition, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Customer Due')</h6>
            <h3 class="text-danger">{{ number_format($customerDue, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Supplier Due')</h6>
            <h3 class="text-danger">{{ number_format($supplierDue, 2) }}</h3>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('retail.reports.inventory', ['type' => 'low_stock']) }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Low Stock Products')</h6>
                <h3 class="{{ $lowStockCount > 0 ? 'text-warning' : '' }}">{{ $lowStockCount }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-3 mb-3">
        <a href="{{ route('retail.reports.inventory', ['type' => 'expiring']) }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Expiring Batches')</h6>
                <h3 class="{{ $expiringCount > 0 ? 'text-warning' : '' }}">{{ $expiringCount }}</h3>
            </div>
        </a>
    </div>
    @if(\ME\Retail\Support\RetailModules::emiEnabled())
    <div class="col-md-3 mb-3">
        <a href="{{ route('retail.reports.emi') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('EMI Overdue Installments')</h6>
                <h3 class="{{ $emiOverdueCount > 0 ? 'text-danger' : '' }}">{{ $emiOverdueCount }}</h3>
            </div>
        </a>
    </div>
    @endif
</div>

<div class="row">
    <div class="col-md-8 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted mb-2">@lang('Sales vs Purchase (last 30 days)')</h6>
            <div id="retail-trend-chart"></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted mb-2">@lang('Payment Method Breakdown (last 30 days)')</h6>
            <div id="retail-payment-chart"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted mb-2">@lang('Sales by Category (last 30 days)')</h6>
            <div id="retail-category-chart"></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted mb-2">@lang('Stock Valuation')</h6>
            <div id="retail-stock-chart"></div>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted mb-2">@lang('Customers by Level')</h6>
            <div id="retail-level-chart"></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card glass-card w-100">
            <div class="card-header"><b>@lang('Top Selling Products (last 30 days)')</b></div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th>@lang('Product')</th>
                            <th class="text-end">@lang('Quantity')</th>
                            <th class="text-end">@lang('Total')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $product)
                            <tr>
                                <td>{{ $product->name }}</td>
                                <td class="text-end">{{ $product->quantity }}</td>
                                <td class="text-end">{{ number_format($product->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center">@lang('No sales in this period')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-3">
        <div class="card glass-card w-100">
            <div class="card-header"><b>@lang('Top Customers (last 30 days)')</b></div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th>@lang('Customer')</th>
                            <th class="text-end">@lang('Total')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topCustomers as $customer)
                            <tr>
                                <td>{{ $customer->name }}</td>
                                <td class="text-end">{{ number_format($customer->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="text-center">@lang('No sales in this period')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<h5 class="mt-2 mb-3">@lang('Catalog & Setup')</h5>
<div class="row">
    @can('retail_store.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.stores.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Stores')</h6>
                <h3>{{ $storeCount }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_category.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.categories.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Categories')</h6>
                <h3>{{ $moduleCounts['categories'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_brand.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.brands.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Brands')</h6>
                <h3>{{ $moduleCounts['brands'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_unit.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.units.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Units')</h6>
                <h3>{{ $moduleCounts['units'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_product.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.products.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Products')</h6>
                <h3>{{ $productCount }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_attribute.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.attributes.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Attributes')</h6>
                <h3>{{ $moduleCounts['attributes'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_warehouse.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.warehouses.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Warehouses')</h6>
                <h3>{{ $moduleCounts['warehouses'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_supplier.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.suppliers.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Suppliers')</h6>
                <h3>{{ $moduleCounts['suppliers'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_customer.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.customers.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Customers')</h6>
                <h3>{{ $moduleCounts['customers'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_membership.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.memberships.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Memberships')</h6>
                <h3>{{ $moduleCounts['memberships'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_customer_level.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.customer-levels.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Customer Levels')</h6>
                <h3>{{ $customerLevels->count() }}</h3>
            </div>
        </a>
    </div>
    @endcan
</div>

<h5 class="mt-2 mb-3">@lang('Transactions')</h5>
<div class="row">
    @can('retail_purchase.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.purchases.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Purchases')</h6>
                <h3>{{ $moduleCounts['purchases'] }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.purchases.returns.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Purchase Returns')</h6>
                <h3>{{ $moduleCounts['purchase_returns'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_sale.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.sales.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Sales (POS)')</h6>
                <h3>{{ $moduleCounts['sales'] }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.sales.returns.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Sale Returns')</h6>
                <h3>{{ $moduleCounts['sale_returns'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_inventory.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.stock-transfers.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Stock Transfers')</h6>
                <h3>{{ $moduleCounts['stock_transfers'] }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.stock-adjustments.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Stock Adjustments')</h6>
                <h3>{{ $moduleCounts['stock_adjustments'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
</div>

<h5 class="mt-2 mb-3">@lang('Finance & Loyalty')</h5>
<div class="row">
    @can('retail_cash_register.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.cash-registers.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Cash Registers')</h6>
                <h3>{{ $moduleCounts['cash_registers'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_expense_category.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.expense-categories.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Expense Categories')</h6>
                <h3>{{ $moduleCounts['expense_categories'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_expense.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.expenses.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Expenses (this month)')</h6>
                <h3 class="text-danger">{{ number_format($expensesThisMonth, 2) }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @if(\ME\Retail\Support\RetailModules::discountEnabled())
    @can('retail_discount_rule.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.discount-rules.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Active Discount Rules')</h6>
                <h3>{{ $moduleCounts['discount_rules'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @endif
    @if(\ME\Retail\Support\RetailModules::loyaltyEnabled())
    @can('retail_loyalty.adjust')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.loyalty.adjust.create') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Points Earned (month)')</h6>
                <h3 class="text-success">{{ number_format($loyaltyThisMonth['earned'], 0) }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.loyalty.adjust.create') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Points Redeemed (month)')</h6>
                <h3 class="text-danger">{{ number_format($loyaltyThisMonth['redeemed'], 0) }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @endif
</div>

<h5 class="mt-2 mb-3">@lang('EMI')</h5>
<div class="row">
    @if(\ME\Retail\Support\RetailModules::emiEnabled())
    @can('retail_emi_bank.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.emi-banks.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('EMI Banks')</h6>
                <h3>{{ $moduleCounts['emi_banks'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_emi_plan.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.emi-plans.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('EMI Plans')</h6>
                <h3>{{ $moduleCounts['emi_plans'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @can('retail_emi.view')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.emi-contracts.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Active EMI Contracts')</h6>
                <h3>{{ $emiSummary['active_contracts'] }}</h3>
            </div>
        </a>
    </div>
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.emi-contracts.index') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Completed EMI Contracts')</h6>
                <h3>{{ $emiSummary['completed_contracts'] }}</h3>
            </div>
        </a>
    </div>
    @endcan
    @endif
    @can('retail_barcode.print')
    <div class="col-md-2 mb-3">
        <a href="{{ route('retail.barcode-labels.form') }}" class="text-decoration-none">
            <div class="card glass-card p-3">
                <h6 class="text-muted">@lang('Print')</h6>
                <h3><i class="fas fa-barcode"></i> @lang('Barcode Labels')</h3>
            </div>
        </a>
    </div>
    @endcan
</div>

@can('retail_inventory.view')
<div class="row">
    <div class="col-md-12 mb-3">
        <div class="card glass-card w-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <b>@lang('Recent Stock Movements')</b>
                <a href="{{ route('retail.stock-ledgers.index') }}" class="text-decoration-none">@lang('View All')</a>
            </div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th>@lang('Product')</th>
                            <th>@lang('Warehouse')</th>
                            <th>@lang('Type')</th>
                            <th class="text-end">@lang('Quantity')</th>
                            <th>@lang('Date')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentStockMovements as $movement)
                            <tr>
                                <td>{{ $movement->product->name ?? '-' }}</td>
                                <td>{{ $movement->warehouse->name ?? '-' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $movement->quantity >= 0 ? 'success' : 'danger' }}">{{ $movement->movement_type->label() }}</span>
                                </td>
                                <td class="text-end">{{ $movement->quantity }}</td>
                                <td>{{ $movement->created_at->format('Y-m-d H:i') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">@lang('No stock movements yet')</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endcan
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const trendLabels   = @json($trendLabels);
    const trendSales    = @json($trendSales);
    const trendPurchase = @json($trendPurchase);

    new ApexCharts(document.querySelector('#retail-trend-chart'), {
        chart: { type: 'area', height: 300, toolbar: { show: false } },
        series: [
            { name: @json(trans('Sales')), data: trendSales },
            { name: @json(trans('Purchase')), data: trendPurchase },
        ],
        xaxis: { categories: trendLabels },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2 },
        colors: ['#28a745', '#dc3545'],
        legend: { position: 'top' },
    }).render();

    const paymentLabels = @json($paymentMethodBreakdown->pluck('label'));
    const paymentTotals  = @json($paymentMethodBreakdown->pluck('total'));

    new ApexCharts(document.querySelector('#retail-payment-chart'), {
        chart: { type: 'donut', height: 260 },
        series: paymentTotals.length ? paymentTotals : [1],
        labels: paymentLabels.length ? paymentLabels : [@json(trans('No data'))],
        legend: { position: 'bottom' },
    }).render();

    const categoryLabels = @json($categoryBreakdown->pluck('label'));
    const categoryTotals = @json($categoryBreakdown->pluck('total'));

    new ApexCharts(document.querySelector('#retail-category-chart'), {
        chart: { type: 'bar', height: 260, toolbar: { show: false } },
        series: [{ name: @json(trans('Sales')), data: categoryTotals }],
        xaxis: { categories: categoryLabels },
        dataLabels: { enabled: false },
        plotOptions: { bar: { borderRadius: 4, horizontal: true } },
    }).render();

    new ApexCharts(document.querySelector('#retail-stock-chart'), {
        chart: { type: 'donut', height: 260 },
        series: [{{ (float) $stockValuation['cost_value'] }}, {{ (float) $stockValuation['retail_value'] }}],
        labels: [@json(trans('Cost Value')), @json(trans('Retail Value'))],
        legend: { position: 'bottom' },
    }).render();

    const levelLabels = @json($customerLevels->pluck('name'));
    const levelCounts  = @json($customerLevels->pluck('customers_count'));

    new ApexCharts(document.querySelector('#retail-level-chart'), {
        chart: { type: 'donut', height: 260 },
        series: levelCounts.length ? levelCounts : [1],
        labels: levelLabels.length ? levelLabels : [@json(trans('No data'))],
        legend: { position: 'bottom' },
    }).render();
});
</script>
@endpush
