@extends('me::master')

@section('title', trans('Retail Dashboard'))

@use('ME\Retail\Support\RetailModules')

@php
    $salesChange = $yesterdaysSales > 0 ? (($todaysSales - $yesterdaysSales) / $yesterdaysSales) * 100 : null;
    $hour = (int) now()->format('G');
    $greeting = $hour < 12 ? __('Good morning') : ($hour < 17 ? __('Good afternoon') : __('Good evening'));

    // Compact "jump to" tiles — one per menu screen, hidden with their feature or permission.
    $shortcutGroups = [
        'Catalog & Setup' => [
            ['Stores', $storeCount, 'retail.stores.index', 'retail_store.view', 'fas fa-store'],
            ['Categories', $moduleCounts['categories'], 'retail.categories.index', 'retail_category.view', 'fas fa-sitemap'],
            ['Brands', $moduleCounts['brands'], 'retail.brands.index', 'retail_brand.view', 'fas fa-copyright'],
            ['Units', $moduleCounts['units'], 'retail.units.index', 'retail_unit.view', 'fas fa-balance-scale'],
            ['Products', $productCount, 'retail.products.index', 'retail_product.view', 'fas fa-box'],
            ['Attributes', $moduleCounts['attributes'], 'retail.attributes.index', 'retail_attribute.view', 'fas fa-swatchbook'],
            ['Warehouses', $moduleCounts['warehouses'], 'retail.warehouses.index', 'retail_warehouse.view', 'fas fa-warehouse'],
            ['Suppliers', $moduleCounts['suppliers'], 'retail.suppliers.index', 'retail_supplier.view', 'fas fa-truck'],
            ['Customers', $moduleCounts['customers'], 'retail.customers.index', 'retail_customer.view', 'fas fa-users'],
            ['Memberships', $moduleCounts['memberships'], 'retail.memberships.index', 'retail_membership.view', 'fas fa-id-card'],
            ['Customer Levels', $customerLevels->count(), 'retail.customer-levels.index', 'retail_customer_level.view', 'fas fa-layer-group'],
        ],
        'Transactions' => [
            ['Sales (POS)', $moduleCounts['sales'], 'retail.sales.index', 'retail_sale.view', 'fas fa-cash-register'],
            ['Sale Returns', $moduleCounts['sale_returns'], 'retail.sales.returns.index', 'retail_sale.view', 'fas fa-undo'],
            ['Purchases', $moduleCounts['purchases'], 'retail.purchases.index', 'retail_purchase.view', 'fas fa-shopping-bag'],
            ['Purchase Returns', $moduleCounts['purchase_returns'], 'retail.purchases.returns.index', 'retail_purchase.view', 'fas fa-reply'],
            ['Stock Transfers', $moduleCounts['stock_transfers'], 'retail.stock-transfers.index', 'retail_inventory.view', 'fas fa-exchange-alt'],
            ['Stock Adjustments', $moduleCounts['stock_adjustments'], 'retail.stock-adjustments.index', 'retail_inventory.view', 'fas fa-sliders-h'],
        ],
        'Finance & Loyalty' => [
            ['Cash Registers', $moduleCounts['cash_registers'], 'retail.cash-registers.index', 'retail_cash_register.view', 'fas fa-cash-register'],
            ['Expense Categories', $moduleCounts['expense_categories'], 'retail.expense-categories.index', 'retail_expense_category.view', 'fas fa-folder'],
            ['Active Discount Rules', $moduleCounts['discount_rules'], 'retail.discount-rules.index', 'retail_discount_rule.view', 'fas fa-percent'],
            ['Points Earned (month)', number_format($loyaltyThisMonth['earned'], 0), 'retail.loyalty.adjust.create', 'retail_loyalty.adjust', 'fas fa-star'],
            ['Points Redeemed (month)', number_format($loyaltyThisMonth['redeemed'], 0), 'retail.loyalty.adjust.create', 'retail_loyalty.adjust', 'fas fa-gift'],
            ['EMI Banks', $moduleCounts['emi_banks'], 'retail.emi-banks.index', 'retail_emi_bank.view', 'fas fa-university'],
            ['EMI Plans', $moduleCounts['emi_plans'], 'retail.emi-plans.index', 'retail_emi_plan.view', 'fas fa-calendar-alt'],
            ['Active EMI Contracts', $emiSummary['active_contracts'], 'retail.emi-contracts.index', 'retail_emi.view', 'fas fa-file-contract'],
        ],
    ];
@endphp

@push('css')
<style>
    .dash .card { border-radius: .9rem; }
    .dash-hero h4 { font-weight: 700; margin: 0; }
    .dash .card.dash-kpi { display: flex; flex-direction: row; gap: .9rem; align-items: center; justify-content: flex-start; text-align: left; padding: 1rem 1.1rem; height: 100%; }
    .dash-kpi > div:last-child { min-width: 0; }
    .dash-kpi-icon { width: 48px; height: 48px; border-radius: .8rem; display: grid; place-items: center; font-size: 1.25rem; flex-shrink: 0; }
    .dash-kpi-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color); }
    .dash-kpi-value { font-size: 1.55rem; font-weight: 800; line-height: 1.15; }
    .dash-kpi-sub { font-size: .78rem; color: var(--bs-secondary-color); }
    .dash-alert { display: flex; align-items: center; gap: .65rem; padding: .7rem .9rem; border-radius: .75rem; text-decoration: none; height: 100%; }
    .dash-alert .count { font-size: 1.3rem; font-weight: 800; }
    .dash-alert small { display: block; line-height: 1.1; }
    .dash-card-title { font-weight: 700; }
    .dash-empty { display: grid; place-items: center; text-align: center; color: var(--bs-secondary-color); min-height: 220px; font-size: .9rem; }
    .dash-empty i { font-size: 1.8rem; opacity: .35; display: block; margin-bottom: .4rem; }
    .dash-rank { width: 26px; height: 26px; border-radius: 50%; display: inline-grid; place-items: center; font-size: .75rem; font-weight: 700; background: var(--bs-tertiary-bg); }
    .dash-list .list-group-item { background: transparent; padding: .55rem .25rem; border-color: var(--bs-border-color-translucent); }
    .dash-bar { height: 5px; border-radius: 3px; background: var(--bs-tertiary-bg); overflow: hidden; }
    .dash-bar span { display: block; height: 100%; background: var(--bs-primary); border-radius: 3px; }
    .dash-shortcut { display: flex; align-items: center; gap: .6rem; padding: .6rem .75rem; border: 1px solid var(--bs-border-color-translucent); border-radius: .7rem; text-decoration: none; color: inherit; background: var(--bs-body-bg); transition: border-color .15s, transform .08s; height: 100%; }
    .dash-shortcut:hover { border-color: var(--bs-primary); transform: translateY(-1px); color: inherit; }
    .dash-shortcut i { width: 30px; height: 30px; border-radius: .5rem; display: grid; place-items: center; background: var(--bs-primary-bg-subtle); color: var(--bs-primary); font-size: .85rem; flex-shrink: 0; }
    .dash-shortcut .label { font-size: .8rem; color: var(--bs-secondary-color); line-height: 1.1; }
    .dash-shortcut .count { font-weight: 700; }
    .dash-section-title { font-size: .8rem; text-transform: uppercase; letter-spacing: .05em; color: var(--bs-secondary-color); font-weight: 700; margin: .25rem 0 .6rem; }
</style>
@endpush

@section('content')
<div class="dash">

@if(!get_setting('retail_business_type'))
    @can('retail_setting.edit')
    <div class="alert alert-info d-flex justify-content-between align-items-center">
        <span>@lang('Tell the system what kind of shop this is to get the right modules, product tracking and units.')</span>
        <a href="{{ route('retail.settings.business-type.edit') }}" class="btn btn-sm btn-primary">@lang('Choose Business Type')</a>
    </div>
    @endcan
@endif

{{-- Greeting and quick actions --}}
<div class="dash-hero d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
    <div>
        <h4>{{ $greeting }}, {{ auth()->user()->name }}</h4>
        <small class="text-muted">{{ now()->translatedFormat('l, d F Y') }}</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @can('retail_sale.create')
            <a href="{{ route('retail.sales.create') }}" class="btn btn-success"><i class="fas fa-cash-register"></i> @lang('New Sale')</a>
        @endcan
        @can('retail_purchase.create')
            <a href="{{ route('retail.purchases.create') }}" class="btn btn-outline-primary"><i class="fas fa-truck-loading"></i> @lang('New Purchase')</a>
        @endcan
        @can('retail_product.create')
            <a href="{{ route('retail.products.create') }}" class="btn btn-outline-secondary"><i class="fas fa-plus"></i> @lang('Add Product')</a>
        @endcan
        @if(RetailModules::enabled('barcode'))
            @can('retail_barcode.print')
                <a href="{{ route('retail.barcode-labels.form') }}" class="btn btn-outline-secondary"><i class="fas fa-barcode"></i> @lang('Print Labels')</a>
            @endcan
        @endif
    </div>
</div>

{{-- Today --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon bg-success-subtle text-success"><i class="fas fa-shopping-cart"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Today\'s Sales')</div>
                <div class="dash-kpi-value">{{ number_format($todaysSales, 2) }}</div>
                <div class="dash-kpi-sub">
                    {{ $todaysSaleCount }} @lang('sales')
                    @if($salesChange !== null)
                        · <span class="{{ $salesChange >= 0 ? 'text-success' : 'text-danger' }}">
                            <i class="fas fa-arrow-{{ $salesChange >= 0 ? 'up' : 'down' }}"></i> {{ number_format(abs($salesChange), 1) }}%
                        </span> @lang('vs yesterday')
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon {{ $todaysProfit >= 0 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }}"><i class="fas fa-chart-line"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Today\'s Profit')</div>
                <div class="dash-kpi-value {{ $todaysProfit >= 0 ? '' : 'text-danger' }}">{{ number_format($todaysProfit, 2) }}</div>
                <div class="dash-kpi-sub">@lang('After cost of goods and expenses')</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon bg-warning-subtle text-warning"><i class="fas fa-truck-loading"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Today\'s Purchase')</div>
                <div class="dash-kpi-value">{{ number_format($todaysPurchase, 2) }}</div>
                <div class="dash-kpi-sub">@lang('Goods received today')</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        @if(RetailModules::enabled('cash_register'))
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon bg-info-subtle text-info"><i class="fas fa-wallet"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Cash in Drawers')</div>
                <div class="dash-kpi-value">{{ number_format($cashPosition, 2) }}</div>
                <div class="dash-kpi-sub">@lang('Open cash register sessions')</div>
            </div>
        </div>
        @else
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon bg-info-subtle text-info"><i class="fas fa-boxes"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Stock Value (retail)')</div>
                <div class="dash-kpi-value">{{ number_format($stockValuation['retail_value'], 2) }}</div>
                <div class="dash-kpi-sub">@lang('At current sale prices')</div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Balances --}}
<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('retail.customers.index') }}" class="text-decoration-none text-reset">
            <div class="card glass-card dash-kpi">
                <div class="dash-kpi-icon bg-danger-subtle text-danger"><i class="fas fa-user-clock"></i></div>
                <div>
                    <div class="dash-kpi-label">@lang('Customer Due')</div>
                    <div class="dash-kpi-value {{ $customerDue > 0 ? 'text-danger' : '' }}">{{ number_format($customerDue, 2) }}</div>
                    <div class="dash-kpi-sub">@lang('To receive from customers')</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <a href="{{ route('retail.suppliers.index') }}" class="text-decoration-none text-reset">
            <div class="card glass-card dash-kpi">
                <div class="dash-kpi-icon bg-secondary-subtle text-secondary"><i class="fas fa-file-invoice-dollar"></i></div>
                <div>
                    <div class="dash-kpi-label">@lang('Supplier Due')</div>
                    <div class="dash-kpi-value {{ $supplierDue > 0 ? 'text-danger' : '' }}">{{ number_format($supplierDue, 2) }}</div>
                    <div class="dash-kpi-sub">@lang('To pay suppliers')</div>
                </div>
            </div>
        </a>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon bg-primary-subtle text-primary"><i class="fas fa-cubes"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Stock Value (cost)')</div>
                <div class="dash-kpi-value">{{ number_format($stockValuation['cost_value'], 2) }}</div>
                <div class="dash-kpi-sub">@lang('Potential margin'): {{ number_format($stockValuation['retail_value'] - $stockValuation['cost_value'], 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        @if(RetailModules::enabled('expense'))
        <a href="{{ route('retail.expenses.index') }}" class="text-decoration-none text-reset">
            <div class="card glass-card dash-kpi">
                <div class="dash-kpi-icon bg-warning-subtle text-warning"><i class="fas fa-receipt"></i></div>
                <div>
                    <div class="dash-kpi-label">@lang('Expenses (this month)')</div>
                    <div class="dash-kpi-value">{{ number_format($expensesThisMonth, 2) }}</div>
                    <div class="dash-kpi-sub">{{ now()->translatedFormat('F Y') }}</div>
                </div>
            </div>
        </a>
        @else
        <div class="card glass-card dash-kpi">
            <div class="dash-kpi-icon bg-success-subtle text-success"><i class="fas fa-store"></i></div>
            <div>
                <div class="dash-kpi-label">@lang('Products')</div>
                <div class="dash-kpi-value">{{ $productCount }}</div>
                <div class="dash-kpi-sub">{{ $storeCount }} @lang('store(s)')</div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Needs attention --}}
<div class="row g-2 mb-3">
    @php($canInventory = auth()->user()->can('retail_report.view'))
    <div class="col-6 col-lg-3">
        <a href="{{ $canInventory ? route('retail.reports.inventory') : '#' }}" class="dash-alert {{ $lowStockCount ? 'bg-warning-subtle text-warning-emphasis' : 'bg-body-tertiary text-body-secondary' }}">
            <i class="fas fa-battery-quarter fa-lg"></i>
            <div><span class="count">{{ $lowStockCount }}</span><small>@lang('Low Stock Products')</small></div>
        </a>
    </div>
    <div class="col-6 col-lg-3">
        <a href="{{ $canInventory ? route('retail.reports.inventory') : '#' }}" class="dash-alert {{ $outOfStockCount ? 'bg-danger-subtle text-danger-emphasis' : 'bg-body-tertiary text-body-secondary' }}">
            <i class="fas fa-ban fa-lg"></i>
            <div><span class="count">{{ $outOfStockCount }}</span><small>@lang('Out of Stock')</small></div>
        </a>
    </div>
    @if(RetailModules::enabled('batch'))
    <div class="col-6 col-lg-3">
        <a href="{{ $canInventory ? route('retail.reports.inventory') : '#' }}" class="dash-alert {{ $expiringCount ? 'bg-danger-subtle text-danger-emphasis' : 'bg-body-tertiary text-body-secondary' }}">
            <i class="fas fa-hourglass-half fa-lg"></i>
            <div><span class="count">{{ $expiringCount }}</span><small>@lang('Expiring Batches')</small></div>
        </a>
    </div>
    @endif
    @if(RetailModules::emiEnabled())
    <div class="col-6 col-lg-3">
        <a href="{{ route('retail.reports.emi') }}" class="dash-alert {{ $emiOverdueCount ? 'bg-danger-subtle text-danger-emphasis' : 'bg-body-tertiary text-body-secondary' }}">
            <i class="fas fa-calendar-times fa-lg"></i>
            <div><span class="count">{{ $emiOverdueCount }}</span><small>@lang('EMI Overdue Installments')</small></div>
        </a>
    </div>
    @endif
</div>

{{-- Trends --}}
<div class="row g-3 mb-3">
    <div class="col-xl-8">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="dash-card-title">@lang('Sales vs Purchase') <small class="text-muted fw-normal">(@lang('last 30 days'))</small></span>
                    @can('retail_report.view')
                        <a href="{{ route('retail.reports.sales') }}" class="small text-decoration-none">@lang('Sales Report') →</a>
                    @endcan
                </div>
                <div id="retail-trend-chart"></div>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="dash-card-title mb-1">@lang('Payment Methods') <small class="text-muted fw-normal">(@lang('30 days'))</small></div>
                <div id="retail-payment-chart"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="{{ RetailModules::loyaltyEnabled() ? 'col-xl-6' : 'col-xl-8' }}">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="dash-card-title mb-1">@lang('Sales by Category') <small class="text-muted fw-normal">(@lang('30 days'))</small></div>
                <div id="retail-category-chart"></div>
            </div>
        </div>
    </div>
    <div class="{{ RetailModules::loyaltyEnabled() ? 'col-md-6 col-xl-3' : 'col-xl-4' }}">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="dash-card-title mb-1">@lang('Stock Valuation')</div>
                <div id="retail-stock-chart"></div>
            </div>
        </div>
    </div>
    @if(RetailModules::loyaltyEnabled())
    <div class="col-md-6 col-xl-3">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="dash-card-title mb-1">@lang('Customers by Level')</div>
                <div id="retail-level-chart"></div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Leaders --}}
@php($topProductMax = max((float) $topProducts->max('total'), 1))
@php($topCustomerMax = max((float) $topCustomers->max('total'), 1))
<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="dash-card-title mb-2"><i class="fas fa-trophy text-warning"></i> @lang('Top Selling Products') <small class="text-muted fw-normal">(@lang('30 days'))</small></div>
                @if($topProducts->isEmpty())
                    <div class="dash-empty" style="min-height:140px"><div><i class="fas fa-box-open"></i>@lang('No sales in this period')</div></div>
                @else
                    <ul class="list-group list-group-flush dash-list">
                        @foreach($topProducts as $i => $row)
                            <li class="list-group-item">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="dash-rank">{{ $i + 1 }}</span>
                                    <span class="flex-grow-1 text-truncate">{{ $row->name }}</span>
                                    <small class="text-muted">{{ +number_format($row->quantity, 2, '.', '') }} @lang('sold')</small>
                                    <b class="ms-2">{{ number_format($row->total, 2) }}</b>
                                </div>
                                <div class="dash-bar mt-1 ms-4"><span style="width: {{ round($row->total / $topProductMax * 100) }}%"></span></div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="dash-card-title mb-2"><i class="fas fa-crown text-primary"></i> @lang('Top Customers') <small class="text-muted fw-normal">(@lang('30 days'))</small></div>
                @if($topCustomers->isEmpty())
                    <div class="dash-empty" style="min-height:140px"><div><i class="fas fa-user-friends"></i>@lang('No sales in this period')</div></div>
                @else
                    <ul class="list-group list-group-flush dash-list">
                        @foreach($topCustomers as $i => $row)
                            <li class="list-group-item">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="dash-rank">{{ $i + 1 }}</span>
                                    <span class="flex-grow-1 text-truncate">{{ $row->name }}</span>
                                    <b>{{ number_format($row->total, 2) }}</b>
                                </div>
                                <div class="dash-bar mt-1 ms-4"><span style="width: {{ round($row->total / $topCustomerMax * 100) }}%"></span></div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Latest activity --}}
<div class="row g-3 mb-3">
    @can('retail_sale.view')
    <div class="col-lg-6">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="dash-card-title"><i class="fas fa-receipt text-success"></i> @lang('Recent Sales')</span>
                    <a href="{{ route('retail.sales.index') }}" class="small text-decoration-none">@lang('View All') →</a>
                </div>
                @if($recentSales->isEmpty())
                    <div class="dash-empty" style="min-height:140px"><div><i class="fas fa-receipt"></i>@lang('No sales yet')</div></div>
                @else
                    <ul class="list-group list-group-flush dash-list">
                        @foreach($recentSales as $sale)
                            <li class="list-group-item">
                                <a href="{{ route('retail.sales.show', $sale->id) }}" class="d-flex align-items-center gap-2 text-decoration-none text-reset">
                                    <span class="flex-grow-1">
                                        <span class="fw-semibold">{{ $sale->invoice_number }}</span>
                                        <small class="text-muted d-block">{{ $sale->customer->name ?? __('Walk-in Customer') }} · {{ $sale->created_at->diffForHumans() }}</small>
                                    </span>
                                    <span class="badge {{ $sale->payment_status->value === 'paid' ? 'bg-success-subtle text-success-emphasis' : ($sale->payment_status->value === 'partial' ? 'bg-warning-subtle text-warning-emphasis' : 'bg-danger-subtle text-danger-emphasis') }}">
                                        {{ ucfirst($sale->payment_status->value) }}
                                    </span>
                                    <b class="ms-2">{{ number_format($sale->grand_total, 2) }}</b>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
    @endcan
    <div class="col-lg-6">
        <div class="card glass-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="dash-card-title"><i class="fas fa-exclamation-triangle text-warning"></i> @lang('Running Low')</span>
                    @can('retail_report.view')
                        <a href="{{ route('retail.reports.inventory') }}" class="small text-decoration-none">@lang('Inventory Report') →</a>
                    @endcan
                </div>
                @if($lowStockProducts->isEmpty() && $expiringBatches->isEmpty())
                    <div class="dash-empty" style="min-height:140px"><div><i class="fas fa-check-circle text-success"></i>@lang('Stock levels look healthy')</div></div>
                @else
                    <ul class="list-group list-group-flush dash-list">
                        @foreach($lowStockProducts->take(6) as $product)
                            <li class="list-group-item d-flex align-items-center gap-2">
                                <i class="fas fa-battery-quarter text-warning"></i>
                                <span class="flex-grow-1 text-truncate">{{ $product->name }}</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis">{{ +number_format($product->stock_quantity, 2, '.', '') }} {{ $product->unit->short_name ?? '' }} @lang('left')</span>
                            </li>
                        @endforeach
                        @if(RetailModules::enabled('batch'))
                            @foreach($expiringBatches->take(4) as $batch)
                                <li class="list-group-item d-flex align-items-center gap-2">
                                    <i class="fas fa-hourglass-half text-danger"></i>
                                    <span class="flex-grow-1 text-truncate">{{ $batch->product->name ?? '' }} <small class="text-muted">· @lang('Batch') {{ $batch->batch_number }}</small></span>
                                    <span class="badge bg-danger-subtle text-danger-emphasis">@lang('Exp') {{ $batch->expiry_date->format('d M') }}</span>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>

@can('retail_inventory.view')
<div class="card glass-card mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="dash-card-title"><i class="fas fa-stream text-info"></i> @lang('Recent Stock Movements')</span>
            <a href="{{ route('retail.stock-ledgers.index') }}" class="small text-decoration-none">@lang('View All') →</a>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
                <thead>
                    <tr class="small text-muted">
                        <th>@lang('Product')</th>
                        <th>@lang('Warehouse')</th>
                        <th>@lang('Type')</th>
                        <th class="text-end">@lang('Quantity')</th>
                        <th class="text-end">@lang('When')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentStockMovements as $movement)
                        <tr>
                            <td>{{ $movement->product->name ?? '-' }}</td>
                            <td class="text-muted">{{ $movement->warehouse->name ?? '-' }}</td>
                            <td><span class="badge {{ $movement->quantity >= 0 ? 'bg-success-subtle text-success-emphasis' : 'bg-danger-subtle text-danger-emphasis' }}">{{ $movement->movement_type->label() }}</span></td>
                            <td class="text-end fw-semibold {{ $movement->quantity >= 0 ? 'text-success' : 'text-danger' }}">{{ $movement->quantity >= 0 ? '+' : '' }}{{ +number_format($movement->quantity, 4, '.', '') }}</td>
                            <td class="text-end text-muted small">{{ $movement->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-3">@lang('No stock movements yet')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endcan

{{-- Jump to any screen --}}
@foreach($shortcutGroups as $groupTitle => $shortcuts)
    @php($visibleShortcuts = collect($shortcuts)->filter(fn ($s) => RetailModules::routeEnabled($s[2]) && auth()->user()->can($s[3])))
    @continue($visibleShortcuts->isEmpty())
    <div class="dash-section-title">@lang($groupTitle)</div>
    <div class="row g-2 mb-3">
        @foreach($visibleShortcuts as [$label, $count, $routeName, $permit, $icon])
            <div class="col-6 col-md-4 col-xl-2">
                <a href="{{ route($routeName) }}" class="dash-shortcut">
                    <i class="{{ $icon }}"></i>
                    <span><span class="label d-block">@lang($label)</span><span class="count">{{ $count }}</span></span>
                </a>
            </div>
        @endforeach
    </div>
@endforeach

</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const css = getComputedStyle(document.documentElement);
    const textColor = css.getPropertyValue('--bs-secondary-color').trim() || '#6c757d';
    const palette = ['#0f9bd6', '#20c997', '#fd7e14', '#6f42c1', '#e83e8c', '#ffc107', '#17a2b8'];
    const base = { fontFamily: 'inherit', foreColor: textColor, toolbar: { show: false }, background: 'transparent' };
    const money = v => Number(v || 0).toLocaleString(undefined, { maximumFractionDigits: 2 });

    // Charts with nothing to show get a friendly empty state instead of a meaningless 100% ring.
    function emptyState(selector, message, icon) {
        document.querySelector(selector).innerHTML = `<div class="dash-empty"><div><i class="fas ${icon}"></i>${message}</div></div>`;
    }

    const trendSales    = @json($trendSales);
    const trendPurchase = @json($trendPurchase);
    new ApexCharts(document.querySelector('#retail-trend-chart'), {
        chart: { ...base, type: 'area', height: 300 },
        series: [
            { name: @json(trans('Sales')), data: trendSales },
            { name: @json(trans('Purchase')), data: trendPurchase },
        ],
        xaxis: { categories: @json($trendLabels), tickAmount: 10, labels: { rotate: 0 } },
        yaxis: { labels: { formatter: money } },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        fill: { type: 'gradient', gradient: { opacityFrom: .35, opacityTo: .02 } },
        colors: ['#20c997', '#fd7e14'],
        grid: { borderColor: 'rgba(127,127,127,.15)', strokeDashArray: 4 },
        legend: { position: 'top', horizontalAlign: 'right' },
        tooltip: { y: { formatter: money } },
    }).render();

    const paymentTotals = @json($paymentMethodBreakdown->pluck('total')->map(fn ($v) => (float) $v));
    if (paymentTotals.length) {
        new ApexCharts(document.querySelector('#retail-payment-chart'), {
            chart: { ...base, type: 'donut', height: 280 },
            series: paymentTotals,
            labels: @json($paymentMethodBreakdown->pluck('label')->map(fn ($l) => ucwords(str_replace('_', ' ', $l)))),
            colors: palette,
            legend: { position: 'bottom' },
            plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: @json(trans('Total')), formatter: w => money(w.globals.seriesTotals.reduce((a, b) => a + b, 0)) } } } } },
            tooltip: { y: { formatter: money } },
        }).render();
    } else {
        emptyState('#retail-payment-chart', @json(trans('No payments in the last 30 days')), 'fa-credit-card');
    }

    const categoryTotals = @json($categoryBreakdown->pluck('total')->map(fn ($v) => (float) $v));
    if (categoryTotals.length) {
        new ApexCharts(document.querySelector('#retail-category-chart'), {
            chart: { ...base, type: 'bar', height: 280 },
            series: [{ name: @json(trans('Sales')), data: categoryTotals }],
            xaxis: { categories: @json($categoryBreakdown->pluck('label')), labels: { formatter: money } },
            dataLabels: { enabled: false },
            colors: ['#0f9bd6'],
            grid: { borderColor: 'rgba(127,127,127,.15)', strokeDashArray: 4 },
            plotOptions: { bar: { borderRadius: 5, horizontal: true, barHeight: '55%' } },
            tooltip: { y: { formatter: money } },
        }).render();
    } else {
        emptyState('#retail-category-chart', @json(trans('No category sales in the last 30 days')), 'fa-sitemap');
    }

    const stockValues = [{{ (float) $stockValuation['cost_value'] }}, {{ (float) $stockValuation['retail_value'] }}];
    if (stockValues.some(v => v > 0)) {
        new ApexCharts(document.querySelector('#retail-stock-chart'), {
            chart: { ...base, type: 'bar', height: 280 },
            series: [{ name: @json(trans('Value')), data: stockValues }],
            xaxis: { categories: [@json(trans('Cost Value')), @json(trans('Retail Value'))] },
            yaxis: { labels: { formatter: money } },
            dataLabels: { enabled: true, formatter: money, style: { fontSize: '11px' } },
            colors: ['#6f42c1', '#20c997'],
            plotOptions: { bar: { borderRadius: 6, columnWidth: '45%', distributed: true } },
            legend: { show: false },
            grid: { borderColor: 'rgba(127,127,127,.15)', strokeDashArray: 4 },
            tooltip: { y: { formatter: money } },
        }).render();
    } else {
        emptyState('#retail-stock-chart', @json(trans('No stock yet')), 'fa-cubes');
    }

    const levelElement = document.querySelector('#retail-level-chart');
    if (levelElement) {
        const levelCounts = @json($customerLevels->pluck('customers_count'));
        if (levelCounts.some(c => c > 0)) {
            new ApexCharts(levelElement, {
                chart: { ...base, type: 'donut', height: 280 },
                series: levelCounts,
                labels: @json($customerLevels->pluck('name')),
                colors: palette,
                legend: { position: 'bottom' },
                plotOptions: { pie: { donut: { size: '68%' } } },
            }).render();
        } else {
            emptyState('#retail-level-chart', @json(trans('No customers in a level yet')), 'fa-layer-group');
        }
    }
});
</script>
@endpush
