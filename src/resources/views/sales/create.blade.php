@extends('me::master')

@section('title', trans('Point of Sale'))

@php
    $features = [
        'barcode'       => \ME\Retail\Support\RetailModules::enabled('barcode'),
        'tax'           => \ME\Retail\Support\RetailModules::enabled('tax'),
        'discount'      => \ME\Retail\Support\RetailModules::enabled('discount'),
        'loyalty'       => \ME\Retail\Support\RetailModules::enabled('loyalty'),
        'membership'    => \ME\Retail\Support\RetailModules::enabled('membership'),
        'hold_sale'     => \ME\Retail\Support\RetailModules::enabled('hold_sale'),
        'cash_register' => \ME\Retail\Support\RetailModules::enabled('cash_register'),
        'warranty'      => \ME\Retail\Support\RetailModules::enabled('warranty'),
    ];
@endphp

@push('css')
<style>
    .pos { --pos-radius: .85rem; }
    .pos .card { border-radius: var(--pos-radius); }
    .pos-topbar .form-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color); margin-bottom: .15rem; }
    .pos-topbar .form-control-plaintext { padding: .25rem 0; }

    .pos-search { position: relative; }
    .pos-search .form-control { height: 3.1rem; font-size: 1.1rem; padding-left: 3rem; border-radius: .7rem; }
    .pos-search .pos-search-icon { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); font-size: 1.25rem; color: var(--bs-secondary-color); }
    .pos-search kbd { position: absolute; right: .75rem; top: 50%; transform: translateY(-50%); font-size: .7rem; opacity: .6; }
    .pos-feedback { min-height: 1.4rem; font-size: .875rem; }

    .pos-categories { display: flex; gap: .4rem; overflow-x: auto; padding-bottom: .25rem; scrollbar-width: thin; }
    .pos-categories .btn { white-space: nowrap; border-radius: 2rem; }

    .pos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: .6rem; max-height: 340px; overflow-y: auto; padding: .15rem; }
    .pos-tile { border: 1px solid var(--bs-border-color); border-radius: .7rem; padding: .6rem; background: var(--bs-body-bg); text-align: left; cursor: pointer; transition: transform .08s, box-shadow .15s, border-color .15s; display: flex; flex-direction: column; gap: .3rem; min-height: 112px; }
    .pos-tile:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.08); border-color: var(--bs-primary); }
    .pos-tile:active { transform: translateY(0); }
    .pos-tile.is-out { opacity: .55; }
    .pos-tile-avatar { width: 34px; height: 34px; border-radius: .55rem; display: grid; place-items: center; font-weight: 700; color: #fff; font-size: .9rem; flex-shrink: 0; }
    .pos-tile-name { font-weight: 600; font-size: .85rem; line-height: 1.15; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .pos-tile-price { font-weight: 700; color: var(--bs-primary); }
    .pos-tile-meta { font-size: .72rem; color: var(--bs-secondary-color); }

    .pos-cart table { margin-bottom: 0; }
    .pos-cart th { font-size: .72rem; text-transform: uppercase; letter-spacing: .04em; color: var(--bs-secondary-color); font-weight: 600; white-space: nowrap; }
    .pos-cart td { vertical-align: middle; }
    .pos-cart .item-name { font-weight: 600; line-height: 1.15; }
    .pos-cart .item-sub { font-size: .75rem; color: var(--bs-secondary-color); }
    .pos-cart .form-control, .pos-cart .form-select { min-width: 70px; }
    .pos-cart .unit-select { min-width: 105px; }
    .pos-qty { display: flex; align-items: center; min-width: 120px; }
    .pos-qty .btn { padding: .2rem .5rem; }
    .pos-qty input { text-align: center; }
    .pos-cart .row-total { font-weight: 700; white-space: nowrap; }
    .pos-cart .tracking-row td { background: var(--bs-tertiary-bg); }
    .pos-empty { padding: 2.5rem 1rem; text-align: center; color: var(--bs-secondary-color); }
    .pos-empty i { font-size: 2rem; opacity: .4; display: block; margin-bottom: .5rem; }

    @media (min-width: 992px) { .pos-side { position: sticky; top: 1rem; } }
    .pos-summary-line { display: flex; justify-content: space-between; align-items: center; padding: .2rem 0; }
    .pos-summary-line .form-control { max-width: 130px; text-align: right; }
    .pos-grand { border-top: 2px dashed var(--bs-border-color); margin-top: .4rem; padding-top: .6rem; }
    .pos-grand .amount { font-size: 2rem; font-weight: 800; line-height: 1; color: var(--bs-primary); }
    .pos-balance { border-radius: .6rem; padding: .5rem .75rem; font-weight: 600; }
    .pos-complete { height: 3.2rem; font-size: 1.1rem; font-weight: 700; border-radius: .7rem; }
    .pos-customer-info .badge { font-weight: 500; }
</style>
@endpush

@section('content')
<div class="pos">

@if($features['hold_sale'] && $heldSales->isNotEmpty())
<div class="collapse" id="held-sales-panel">
    <div class="card glass-card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <b><i class="fas fa-pause-circle text-warning"></i> @lang('Held Sales')</b>
            <button type="button" class="btn-close" data-bs-toggle="collapse" data-bs-target="#held-sales-panel" aria-label="@lang('Close')"></button>
        </div>
        <div class="card-body p-2">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>@lang('Reference')</th>
                            <th>@lang('Customer')</th>
                            <th class="text-center">@lang('Items')</th>
                            <th class="text-end">@lang('Approx. Total')</th>
                            <th>@lang('Held By')</th>
                            <th>@lang('Held At')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($heldSales as $held)
                            <tr class="{{ $resumedSale?->id === $held->id ? 'table-warning' : '' }}">
                                <td class="fw-semibold">{{ $held->reference ?: '#' . $held->id }}</td>
                                <td>{{ $held->customer->name ?? __('Walk-in Customer') }}</td>
                                <td class="text-center">{{ $held->item_count }}</td>
                                <td class="text-end">{{ number_format($held->estimated_total, 2) }}</td>
                                <td>{{ $held->user->name ?? '' }}</td>
                                <td class="text-muted small">{{ $held->created_at->diffForHumans() }}</td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('retail.sales.create', ['resume' => $held->id]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-play"></i> @lang('Resume')
                                    </a>
                                    <form action="{{ route('retail.held-sales.destroy', $held->id) }}" method="POST" class="d-inline" onsubmit="return confirm(@json(__('Discard this held sale?')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="@lang('Discard')"><i class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endif

<form action="{{ route('retail.sales.store') }}" method="POST" id="sale-form" autocomplete="off">
    @csrf
    @if($resumedSale)
        <input type="hidden" name="held_sale_id" value="{{ $resumedSale->id }}">
    @endif

    {{-- Top bar: where and when this sale happens --}}
    <div class="card glass-card pos-topbar mb-3">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-sm-6 col-lg-3">
                    @include('retail::partials._store-select', ['stores' => $stores])
                </div>
                <div class="col-sm-6 col-lg-3">
                    @include('retail::partials._warehouse-select', ['warehouses' => $warehouses])
                </div>
                <div class="col-sm-6 col-lg-2">
                    <label class="form-label">@lang('Sale Date')</label>
                    <input type="date" name="sale_date" class="form-control form-control-sm" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-sm-6 col-lg-4 text-lg-end">
                    @if($resumedSale)
                        <span class="badge bg-warning text-dark me-1"><i class="fas fa-play"></i> @lang('Resumed'): {{ $resumedSale->reference ?: '#' . $resumedSale->id }}</span>
                    @endif
                    @if($features['hold_sale'] && $heldSales->isNotEmpty())
                        <button type="button" class="btn btn-sm btn-outline-warning" data-bs-toggle="collapse" data-bs-target="#held-sales-panel">
                            <i class="fas fa-pause-circle"></i> @lang('Held Sales') <span class="badge bg-warning text-dark">{{ $heldSales->count() }}</span>
                        </button>
                    @endif
                    <a href="{{ route('retail.sales.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-list"></i> @lang('Sales')</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Left: find products, then the cart --}}
        <div class="col-lg-7 col-xl-8">
            <div class="card glass-card mb-3">
                <div class="card-body">
                    <div class="pos-search mb-1">
                        @if($features['barcode'])
                            <i class="fas fa-barcode pos-search-icon"></i>
                            <input type="text" id="barcode-scan" class="form-control pos-search-input" autofocus
                                   placeholder="@lang('Scan a barcode, or type to search products')">
                        @else
                            <i class="fas fa-search pos-search-icon"></i>
                            <input type="text" id="product-search" class="form-control pos-search-input"
                                   placeholder="@lang('Search products by name or SKU')">
                        @endif
                        <kbd>F2</kbd>
                    </div>
                    <div id="barcode-scan-feedback" class="pos-feedback mb-2"></div>

                    <div class="pos-categories mb-2" id="category-filter">
                        <button type="button" class="btn btn-sm btn-primary" data-category="">@lang('All')</button>
                        @foreach($categories as $category)
                            <button type="button" class="btn btn-sm btn-outline-secondary" data-category="{{ $category->id }}">{{ $category->name }}</button>
                        @endforeach
                    </div>

                    <div class="pos-grid" id="product-grid"></div>
                </div>
            </div>

            <div class="card glass-card pos-cart">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <b><i class="fas fa-shopping-cart"></i> @lang('Cart')</b>
                    <span class="badge bg-secondary" id="cart-count">0</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm align-middle" id="items-table">
                        <thead>
                            <tr>
                                <th style="min-width:180px">@lang('Item')</th>
                                <th>@lang('Unit')</th>
                                <th class="text-center">@lang('Qty')</th>
                                <th>@lang('Price')</th>
                                <th>@lang('Disc.')</th>
                                @if($features['tax'])<th>@lang('Tax')</th>@endif
                                <th class="text-end">@lang('Total')</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody id="items-body"></tbody>
                    </table>
                </div>
                <div class="pos-empty" id="cart-empty">
                    <i class="fas fa-shopping-basket"></i>
                    {{ $features['barcode'] ? __('Scan a barcode or tap a product to start the sale.') : __('Tap a product or search to start the sale.') }}
                </div>
            </div>
        </div>

        {{-- Right: customer, totals, payment --}}
        <div class="col-lg-5 col-xl-4">
            <div class="pos-side">
                <div class="card glass-card mb-3">
                    <div class="card-body">
                        <label class="form-label fw-semibold mb-1"><i class="fas fa-user"></i> @lang('Customer')</label>
                        <select name="customer_id" id="customer-select" class="form-select">
                            <option value="">@lang('Walk-in Customer')</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}{{ $customer->phone ? ' — ' . $customer->phone : '' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="pos-customer-info small mt-2" id="customer-info"></div>
                    </div>
                </div>

                <div class="card glass-card mb-3">
                    <div class="card-body">
                        <div class="pos-summary-line">
                            <span class="text-muted">@lang('Subtotal') (<span id="summary-items">0</span> @lang('items'))</span>
                            <b id="summary-subtotal">0.00</b>
                        </div>
                        <div class="pos-summary-line">
                            <span class="text-muted">@lang('Invoice Discount')</span>
                            <input type="number" step="0.01" min="0" name="discount" class="form-control form-control-sm pos-total-input" value="{{ old('discount', 0) }}">
                        </div>
                        @if($features['tax'])
                        <div class="pos-summary-line">
                            <span class="text-muted">@lang('Invoice Tax')</span>
                            <input type="number" step="0.01" min="0" name="tax" class="form-control form-control-sm pos-total-input" value="{{ old('tax', 0) }}">
                        </div>
                        @endif
                        @if($features['discount'])
                        <div class="pos-summary-line">
                            <span class="text-muted">@lang('Coupon Code')</span>
                            <input type="text" name="coupon_code" class="form-control form-control-sm" value="{{ old('coupon_code') }}">
                        </div>
                        @endif
                        @if($features['loyalty'])
                        <div class="pos-summary-line" id="redeem-points-line">
                            <span class="text-muted">@lang('Redeem Points') <small id="points-available-hint" class="d-block"></small></span>
                            <input type="number" step="0.01" min="0" name="redeem_points" id="redeem-points-input" class="form-control form-control-sm pos-total-input" value="{{ old('redeem_points', 0) }}">
                        </div>
                        @endif

                        <div class="pos-grand d-flex justify-content-between align-items-end">
                            <div>
                                <div class="text-muted small">@lang('Grand Total')</div>
                                @if($features['discount'] || $features['loyalty'] || $features['membership'])
                                    <small class="text-muted" style="font-size:.7rem">@lang('Automatic discounts apply on save')</small>
                                @endif
                            </div>
                            <div class="amount" id="summary-grand">0.00</div>
                        </div>
                    </div>
                </div>

                <div class="card glass-card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <b><i class="fas fa-money-bill-wave"></i> @lang('Payment')</b>
                            <div class="btn-group btn-group-sm">
                                <button type="button" id="pay-exact" class="btn btn-outline-success">@lang('Exact')</button>
                                <button type="button" id="add-payment-row" class="btn btn-outline-secondary"><i class="fas fa-plus"></i> @lang('Split')</button>
                            </div>
                        </div>
                        @if($features['cash_register'] && $openSessions->isNotEmpty())
                        <select name="cash_register_session_id" class="form-select form-select-sm mb-2">
                            <option value="">@lang('No cash drawer')</option>
                            @foreach($openSessions as $session)
                                <option value="{{ $session->id }}" {{ (old('cash_register_session_id') == $session->id || $openSessions->count() === 1) ? 'selected' : '' }}>
                                    @lang('Drawer'): {{ $session->cashRegister->name }}
                                </option>
                            @endforeach
                        </select>
                        @endif
                        <div id="payments-body"></div>

                        <div class="pos-summary-line mt-2">
                            <span class="text-muted">@lang('Cash received')</span>
                            <input type="number" step="0.01" min="0" id="cash-received" class="form-control form-control-sm" placeholder="0.00">
                        </div>
                        <div class="pos-balance mt-2 bg-body-tertiary d-flex justify-content-between" id="balance-box">
                            <span id="balance-label">@lang('Due')</span>
                            <span id="balance-amount">0.00</span>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size:.72rem">@lang('A walk-in sale must be paid in full; a registered customer can buy on credit.')</small>
                    </div>
                </div>

                <div class="mb-2">
                    <a class="small text-decoration-none" data-bs-toggle="collapse" href="#sale-notes"><i class="fas fa-sticky-note"></i> @lang('Add note')</a>
                    <div class="collapse {{ old('notes') ? 'show' : '' }}" id="sale-notes">
                        <textarea name="notes" class="form-control form-control-sm mt-1" rows="2">{{ old('notes') }}</textarea>
                    </div>
                </div>

                <button type="submit" id="complete-sale" class="btn btn-success w-100 pos-complete">
                    <i class="fas fa-check-circle"></i> @lang('Complete Sale') <small class="opacity-75">(F9)</small>
                </button>

                @if($features['hold_sale'])
                <div class="input-group input-group-sm mt-2">
                    <input type="text" name="hold_reference" class="form-control" value="{{ $resumedSale->reference ?? '' }}" placeholder="@lang('Hold reference (optional)')">
                    <button type="submit" formaction="{{ route('retail.sales.hold') }}" formnovalidate class="btn btn-warning" id="hold-sale">
                        <i class="fas fa-pause"></i> @lang('Hold Sale')
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</form>
</div>
@endsection

@php
    // Computed into plain variables first — Blade's @json() splits its argument on every
    // top-level comma, so an inline multi-key array literal must never be passed directly.
    $retailProductsData = $products->map(fn($p) => [
        'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'barcode' => $p->barcode,
        'category_id' => $p->category_id,
        'unit_id' => $p->unit_id, 'unit_name' => $p->unit->short_name ?? ($p->unit->name ?? ''),
        'sale_price' => (float) $p->sale_price,
        'track_type' => $p->track_type,
    ]);
    $retailUnitsData = $units->map(fn($u) => [
        'id' => $u->id, 'name' => $u->name, 'base_unit_id' => $u->base_unit_id,
    ]);
    $retailBatchesData = $batches->map(fn($b) => [
        'id' => $b->id, 'product_id' => $b->product_id, 'warehouse_id' => $b->warehouse_id,
        'batch_number' => $b->batch_number, 'expiry_date' => $b->expiry_date?->format('Y-m-d'),
        'quantity' => (float) $b->quantity,
    ])->sortBy(fn ($b) => $b['expiry_date'] ?? '9999-12-31')->values();
    $retailSerialsData = $serials->map(fn($s) => [
        'id' => $s->id, 'product_id' => $s->product_id, 'warehouse_id' => $s->warehouse_id,
        'serial_number' => $s->serial_number, 'warranty_end' => $s->warranty_end?->format('Y-m-d'),
    ]);
    $retailCustomersData = $customers->map(fn($c) => [
        'id' => $c->id, 'points_balance' => (float) $c->points_balance,
        'membership' => $c->hasActiveMembership() ? ($c->membership->name ?? null) : null,
        'level' => $c->level->name ?? null,
        'due' => (float) $c->due_balance,
    ]);
    $retailStocksData = $stocks->map(fn($s) => [
        'product_id' => $s->product_id, 'warehouse_id' => $s->warehouse_id, 'quantity' => (float) $s->quantity,
    ]);
    $resumedPayload = $resumedSale?->payload;
    $posText = [
        'selectWarehouse'   => __('Select a warehouse first.'),
        'lookupFailed'      => __('Barcode lookup failed.'),
        'notForSale'        => __('This product is not available for sale.'),
        'scanSerial'        => __('select or scan the serial / IMEI below.'),
        'serialUnavailable' => __('This serial is not available in the selected warehouse.'),
        'alreadyAdded'      => __('already added.'),
        'noBatch'           => __('no batch with stock in this warehouse.'),
        'batchUnavailable'  => __('This batch is not available in the selected warehouse.'),
        'batch'             => __('Batch'),
        'notFound'          => __('No product matches'),
        'pickOne'           => __('Several products match — tap one below.'),
        'noProducts'        => __('No products found.'),
        'emptyCart'         => __('Add at least one item to the cart.'),
        'stock'             => __('Stock'),
        'due'               => __('Due'),
        'change'            => __('Change'),
        'overpaid'          => __('Payment exceeds total by'),
        'paid'              => __('Fully paid'),
        'points'            => __('points'),
        'availablePoints'   => __('Available points'),
        'warrantyUntil'     => __('warranty until'),
        'noWarranty'        => __('no warranty'),
        'selectBatch'       => __('Select Batch'),
        'batchLabel'        => __('Batch (earliest expiry first)'),
        'serialLabel'       => __('Serials / IMEI (selected count becomes the quantity)'),
        'available'         => __('available'),
        'remove'            => __('Remove'),
        'previousDue'       => __('Previous due'),
        'cash'              => __('Cash'),
        'bank'              => __('Bank'),
        'card'              => __('Card'),
        'mobileBanking'     => __('Mobile Banking'),
        'other'             => __('Other'),
        'reference'         => __('Reference'),
    ];
@endphp
@push('js')
<script>
    const retailProducts = @json($retailProductsData);
    const retailUnits = @json($retailUnitsData);
    const retailBatches = @json($retailBatchesData);
    const retailSerials = @json($retailSerialsData);
    const retailCustomers = @json($retailCustomersData);
    const retailStocks = @json($retailStocksData);
    const posFeatures = @json($features);
    const posText = @json($posText);
    const pointValue = @json($pointValue);

    const saleForm = document.getElementById('sale-form');
    const itemsBody = document.getElementById('items-body');
    const searchInput = document.querySelector('.pos-search-input');
    const scanFeedback = document.getElementById('barcode-scan-feedback');
    const productGrid = document.getElementById('product-grid');
    const columnCount = document.querySelectorAll('#items-table thead th').length;

    const money = n => (Math.round((Number(n) || 0) * 100) / 100).toFixed(2);
    const escapeHtml = s => String(s ?? '').replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function warehouseId() {
        return document.querySelector('[name="warehouse_id"]').value;
    }

    function stockOf(productId) {
        const wid = warehouseId();
        return retailStocks
            .filter(s => s.product_id == productId && (!wid || s.warehouse_id == wid))
            .reduce((sum, s) => sum + s.quantity, 0);
    }

    function showScanFeedback(message, isSuccess) {
        scanFeedback.textContent = message;
        scanFeedback.className = 'pos-feedback mb-2 ' + (isSuccess ? 'text-success' : 'text-danger');
    }

    function focusSearch() {
        // Keeps the scanner ready: after every add, focus returns to the scan box.
        if (posFeatures.barcode && searchInput) searchInput.focus();
    }

    // ---------- Customer ----------
    function refreshCustomerInfo() {
        const customer = retailCustomers.find(c => c.id == document.getElementById('customer-select').value);
        const info = document.getElementById('customer-info');
        const hint = document.getElementById('points-available-hint');
        const badges = [];

        if (customer) {
            if (posFeatures.loyalty) badges.push(`<span class="badge bg-success-subtle text-success-emphasis"><i class="fas fa-star"></i> ${money(customer.points_balance)} ${posText.points}</span>`);
            if (posFeatures.loyalty && customer.level) badges.push(`<span class="badge bg-info-subtle text-info-emphasis">${escapeHtml(customer.level)}</span>`);
            if (posFeatures.membership && customer.membership) badges.push(`<span class="badge bg-warning-subtle text-warning-emphasis"><i class="fas fa-id-card"></i> ${escapeHtml(customer.membership)}</span>`);
            if (customer.due > 0) badges.push(`<span class="badge bg-danger-subtle text-danger-emphasis">${posText.previousDue}: ${money(customer.due)}</span>`);
        }
        info.innerHTML = badges.join(' ');
        if (hint) hint.textContent = customer ? `${posText.availablePoints}: ${money(customer.points_balance)}` : '';

        const redeemLine = document.getElementById('redeem-points-line');
        if (redeemLine) redeemLine.style.display = customer ? '' : 'none';
        recalcTotals();
    }

    // ---------- Product grid ----------
    let activeCategory = '';

    function tileColor(product) {
        const hue = ((product.category_id || product.id) * 47) % 360;
        return `hsl(${hue} 55% 48%)`;
    }

    function renderGrid() {
        const term = (searchInput?.value || '').trim().toLowerCase();
        const matches = retailProducts.filter(p =>
            (!activeCategory || p.category_id == activeCategory)
            && (!term || p.name.toLowerCase().includes(term) || (p.sku || '').toLowerCase().includes(term) || (p.barcode || '').toLowerCase().includes(term))
        );

        if (!matches.length) {
            productGrid.innerHTML = `<div class="text-muted small p-3">${posText.noProducts}</div>`;
            return matches;
        }

        productGrid.innerHTML = matches.slice(0, 60).map(p => {
            const stock = stockOf(p.id);
            const trackIcon = p.track_type === 'serial' ? '<i class="fas fa-fingerprint" title="Serial"></i>' : (p.track_type === 'batch' ? '<i class="fas fa-layer-group" title="Batch"></i>' : '');
            return `
                <button type="button" class="pos-tile ${stock <= 0 ? 'is-out' : ''}" data-product-id="${p.id}">
                    <div class="d-flex gap-2 align-items-start">
                        <span class="pos-tile-avatar" style="background:${tileColor(p)}">${escapeHtml(p.name.charAt(0).toUpperCase())}</span>
                        <span class="pos-tile-name">${escapeHtml(p.name)}</span>
                    </div>
                    <div class="mt-auto d-flex justify-content-between align-items-end">
                        <span class="pos-tile-price">${money(p.sale_price)}</span>
                        <span class="pos-tile-meta">${trackIcon} ${posText.stock}: ${+stock.toFixed(2)} ${escapeHtml(p.unit_name)}</span>
                    </div>
                </button>`;
        }).join('');
        return matches;
    }

    document.getElementById('category-filter').addEventListener('click', function (e) {
        const button = e.target.closest('[data-category]');
        if (!button) return;
        activeCategory = button.dataset.category;
        this.querySelectorAll('[data-category]').forEach(b => {
            b.classList.toggle('btn-primary', b === button);
            b.classList.toggle('btn-outline-secondary', b !== button);
        });
        renderGrid();
        focusSearch();
    });

    productGrid.addEventListener('click', function (e) {
        const tile = e.target.closest('.pos-tile');
        if (!tile) return;
        const product = retailProducts.find(p => p.id == tile.dataset.productId);
        if (product) addProduct(product);
        focusSearch();
    });

    // ---------- Cart rows ----------
    let itemIndex = 0;

    function unitsForProduct(productId) {
        const product = retailProducts.find(p => p.id == productId);
        if (!product) return [];
        return retailUnits.filter(u => u.id == product.unit_id || u.base_unit_id == product.unit_id);
    }

    function itemRows() {
        return Array.from(itemsBody.querySelectorAll('tr.item-row'));
    }

    function trackingRowFor(row) {
        return itemsBody.querySelector(`tr.tracking-row[data-index="${row.dataset.index}"]`);
    }

    function batchSelectFor(row) {
        return trackingRowFor(row)?.querySelector('select[name$="[batch_id]"]');
    }

    function productOfRow(row) {
        return retailProducts.find(p => p.id == row.querySelector('.product-select').value);
    }

    function buildItemRow(index, product) {
        const row = document.createElement('tr');
        row.className = 'item-row';
        row.dataset.index = index;
        const trackBadge = product.track_type === 'serial' ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis">IMEI</span>'
            : (product.track_type === 'batch' ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis">Batch</span>' : '');
        row.innerHTML = `
            <td>
                <input type="hidden" name="items[${index}][product_id]" class="product-select" value="${product.id}">
                <div class="item-name">${escapeHtml(product.name)}${trackBadge}</div>
                <div class="item-sub">${escapeHtml(product.sku || '')}</div>
            </td>
            <td><select name="items[${index}][unit_id]" class="form-select form-select-sm unit-select" required></select></td>
            <td>
                <div class="pos-qty input-group input-group-sm">
                    <button type="button" class="btn btn-outline-secondary qty-minus">−</button>
                    <input type="number" step="0.0001" min="0.0001" name="items[${index}][quantity]" class="form-control qty-input" value="0" required>
                    <button type="button" class="btn btn-outline-secondary qty-plus">+</button>
                </div>
            </td>
            <td><input type="number" step="0.01" min="0" name="items[${index}][sale_price]" class="form-control form-control-sm price-input" required></td>
            <td><input type="number" step="0.01" min="0" name="items[${index}][discount]" class="form-control form-control-sm discount-input" value="0"></td>
            ${posFeatures.tax ? `<td><input type="number" step="0.01" min="0" name="items[${index}][tax]" class="form-control form-control-sm tax-input" value="0"></td>` : ''}
            <td class="row-total text-end">0.00</td>
            <td class="text-end">
                <button type="button" class="btn btn-sm btn-link text-danger remove-row" title="${posText.remove}"><i class="fas fa-times"></i></button>
            </td>
        `;
        return row;
    }

    function buildTrackingRow(index) {
        const row = document.createElement('tr');
        row.classList.add('tracking-row');
        row.dataset.index = index;
        row.style.display = 'none';
        row.innerHTML = `<td colspan="${columnCount}" class="tracking-cell"></td>`;
        return row;
    }

    function addProductRow(product) {
        const index = itemIndex++;
        const row = buildItemRow(index, product);
        itemsBody.appendChild(row);
        itemsBody.appendChild(buildTrackingRow(index));
        refreshUnitOptions(row);
        row.querySelector('.unit-select').value = product.unit_id;
        updateCartState();
        return row;
    }

    function refreshUnitOptions(row) {
        const product = productOfRow(row);
        const unitSelect = row.querySelector('.unit-select');
        unitSelect.innerHTML = unitsForProduct(product?.id).map(u => `<option value="${u.id}">${escapeHtml(u.name)}</option>`).join('');
        if (product) row.querySelector('.price-input').value = product.sale_price;
        refreshTrackingRow(row, product);
    }

    function refreshTrackingRow(row, product) {
        const trackingRow = trackingRowFor(row);
        const cell = trackingRow.querySelector('.tracking-cell');
        const wid = warehouseId();
        const index = row.dataset.index;

        if (!product || product.track_type === 'normal') {
            trackingRow.style.display = 'none';
            cell.innerHTML = '';
            return;
        }

        trackingRow.style.display = '';

        if (product.track_type === 'batch') {
            const options = retailBatches.filter(b => b.product_id == product.id && (!wid || b.warehouse_id == wid) && b.quantity > 0);
            cell.innerHTML = `
                <div class="d-flex align-items-center gap-2 small">
                    <span class="text-muted text-nowrap"><i class="fas fa-layer-group"></i> ${posText.batchLabel}</span>
                    <select name="items[${index}][batch_id]" class="form-select form-select-sm" required>
                        <option value="">${posText.selectBatch}</option>
                        ${options.map(b => `<option value="${b.id}">${escapeHtml(b.batch_number)}${b.expiry_date ? ' — exp ' + b.expiry_date : ''} (${b.quantity} ${posText.available})</option>`).join('')}
                    </select>
                </div>`;
        } else if (product.track_type === 'serial') {
            const options = retailSerials.filter(s => s.product_id == product.id && (!wid || s.warehouse_id == wid));
            cell.innerHTML = `
                <div class="small">
                    <span class="text-muted"><i class="fas fa-fingerprint"></i> ${posText.serialLabel}</span>
                    <select class="form-select form-select-sm serial-select mt-1" multiple size="3">
                        ${options.map(s => `<option value="${s.id}">${escapeHtml(s.serial_number)}${posFeatures.warranty ? (s.warranty_end ? ' — ' + posText.warrantyUntil + ' ' + s.warranty_end : ' — ' + posText.noWarranty) : ''}</option>`).join('')}
                    </select>
                    <div class="serial-inputs"></div>
                </div>`;
        }
    }

    function syncSerialSelection(row) {
        const index = row.dataset.index;
        const select = trackingRowFor(row)?.querySelector('.serial-select');
        if (!select) return;

        const selected = Array.from(select.selectedOptions).map(o => o.value);
        trackingRowFor(row).querySelector('.serial-inputs').innerHTML =
            selected.map(id => `<input type="hidden" name="items[${index}][serial_ids][]" value="${id}">`).join('');

        row.querySelector('.qty-input').value = selected.length;
        recalculateRow(row);
    }

    function recalculateRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input')?.value) || 0;
        row.querySelector('.row-total').textContent = money((qty * price) - discount + tax);
        recalcTotals();
    }

    function addQuantity(row, quantity) {
        const qtyInput = row.querySelector('.qty-input');
        qtyInput.value = +((parseFloat(qtyInput.value) || 0) + quantity).toFixed(4);
        recalculateRow(row);
        row.classList.add('table-success');
        setTimeout(() => row.classList.remove('table-success'), 350);
    }

    function updateCartState() {
        const count = itemRows().length;
        document.getElementById('cart-empty').style.display = count ? 'none' : '';
        document.getElementById('cart-count').textContent = count;
        recalcTotals();
    }

    // An existing line for the same product in its base unit (and the same lot, for batch products).
    function findScannedRow(product, batchId) {
        return itemRows().find(row =>
            row.querySelector('.product-select').value == product.id
            && row.querySelector('.unit-select').value == product.unit_id
            && (!batchId || batchSelectFor(row)?.value == batchId)
        );
    }

    /**
     * Adds a product to the cart from a tile tap or a scan.
     * options: quantity (default 1), batchId, serialId, separateLine (weighed packs).
     */
    function addProduct(product, options = {}) {
        if (!warehouseId()) {
            showScanFeedback(posText.selectWarehouse, false);
            return;
        }

        const quantity = options.quantity ?? 1;
        const note = options.note ?? '';

        if (product.track_type === 'serial') {
            const row = itemRows().find(r => r.querySelector('.product-select').value == product.id) || addProductRow(product);
            const select = trackingRowFor(row).querySelector('.serial-select');
            if (!options.serialId) {
                showScanFeedback(`${product.name}: ${posText.scanSerial}`, true);
                select?.focus();
                return;
            }
            const option = Array.from(select?.options ?? []).find(o => o.value == options.serialId);
            if (!option) {
                showScanFeedback(posText.serialUnavailable, false);
                return;
            }
            if (option.selected) {
                showScanFeedback(`${option.textContent.split(' — ')[0]}: ${posText.alreadyAdded}`, false);
                return;
            }
            option.selected = true;
            syncSerialSelection(row);
            showScanFeedback(`${product.name} — ${option.textContent.split(' — ')[0]}`, true);
            return;
        }

        if (product.track_type === 'batch') {
            // A tile tap picks the earliest-expiring lot with stock (FEFO), like a product-barcode scan.
            const batchId = options.batchId
                ?? retailBatches.find(b => b.product_id == product.id && b.warehouse_id == warehouseId() && b.quantity > 0)?.id;
            if (!batchId) {
                showScanFeedback(`${product.name}: ${posText.noBatch}`, false);
                return;
            }
            let row = options.separateLine ? null : findScannedRow(product, batchId);
            if (!row) {
                row = addProductRow(product);
                const batchSelect = batchSelectFor(row);
                batchSelect.value = batchId;
                if (batchSelect.value != batchId) {
                    trackingRowFor(row)?.remove();
                    row.remove();
                    updateCartState();
                    showScanFeedback(posText.batchUnavailable, false);
                    return;
                }
            }
            addQuantity(row, quantity);
            const batch = retailBatches.find(b => b.id == batchId);
            showScanFeedback(`${product.name} — ${posText.batch} ${batch?.batch_number ?? ''}${note}`, true);
            return;
        }

        addQuantity((options.separateLine ? null : findScannedRow(product)) || addProductRow(product), quantity);
        showScanFeedback(product.name + note, true);
    }

    itemsBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('serial-select')) {
            syncSerialSelection(e.target.closest('tr.tracking-row').previousElementSibling);
            return;
        }
        const row = e.target.closest('tr.item-row');
        if (row) recalculateRow(row);
    });

    itemsBody.addEventListener('input', function (e) {
        const row = e.target.closest('tr.item-row');
        if (row) recalculateRow(row);
    });

    itemsBody.addEventListener('click', function (e) {
        const row = e.target.closest('tr.item-row');
        if (!row) return;

        if (e.target.closest('.qty-plus') || e.target.closest('.qty-minus')) {
            const qtyInput = row.querySelector('.qty-input');
            const step = e.target.closest('.qty-plus') ? 1 : -1;
            qtyInput.value = Math.max(+((parseFloat(qtyInput.value) || 0) + step).toFixed(4), 0);
            recalculateRow(row);
            return;
        }

        if (e.target.closest('.remove-row')) {
            trackingRowFor(row)?.remove();
            row.remove();
            updateCartState();
            focusSearch();
        }
    });

    // A scanner's trailing Enter inside a cart field must not submit the sale.
    itemsBody.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.preventDefault();
    });

    document.querySelector('[name="warehouse_id"]').addEventListener('change', function () {
        itemRows().forEach(row => refreshTrackingRow(row, productOfRow(row)));
        renderGrid();
    });

    // ---------- Search / scan ----------
    const barcodeLookupUrl = @json(route('retail.barcode.lookup'));

    async function lookupCode(code) {
        try {
            const params = new URLSearchParams({ code, context: 'sale', warehouse_id: warehouseId() });
            const response = await fetch(`${barcodeLookupUrl}?${params}`, { headers: { Accept: 'application/json' } });
            const data = await response.json();
            return response.ok && data?.found ? data : null;
        } catch (e) {
            return null;
        }
    }

    // The server resolves the code (product barcode, scale label, lot barcode, IMEI or SKU).
    async function handleScan(code) {
        if (!warehouseId()) {
            showScanFeedback(posText.selectWarehouse, false);
            return false;
        }

        const data = await lookupCode(code);
        if (!data) return false;

        const product = retailProducts.find(p => p.id == data.product.id);
        if (!product) {
            showScanFeedback(posText.notForSale, false);
            return true;
        }

        // A weighing-scale label carries its own quantity; each weighed pack gets its own line.
        const isWeighed = data.match === 'weighed';
        addProduct(product, {
            quantity: isWeighed ? data.quantity : 1,
            batchId: data.batch?.id ?? null,
            serialId: data.serial?.id ?? null,
            separateLine: isWeighed,
            note: isWeighed ? ` — ${data.quantity} ${product.unit_name}` : '',
        });
        return true;
    }

    searchInput.addEventListener('input', renderGrid);

    searchInput.addEventListener('keydown', async function (e) {
        if (e.key === 'Escape') {
            searchInput.value = '';
            renderGrid();
            return;
        }
        if (e.key !== 'Enter') return;
        e.preventDefault();

        const code = searchInput.value.trim();
        if (code === '') return;

        if (posFeatures.barcode && await handleScan(code)) {
            searchInput.value = '';
            renderGrid();
            return;
        }

        // Not a known code: fall back to the search results.
        const matches = renderGrid();
        if (matches.length === 1) {
            addProduct(matches[0]);
            searchInput.value = '';
            renderGrid();
        } else {
            showScanFeedback(matches.length ? posText.pickOne : `${posText.notFound} "${code}"`, false);
        }
    });

    // ---------- Totals & payment ----------
    let paymentIndex = 0;
    const paymentsBody = document.getElementById('payments-body');

    function grandTotal() {
        const subtotal = itemRows().reduce((sum, row) => sum + (parseFloat(row.querySelector('.row-total').textContent) || 0), 0);
        const discount = parseFloat(saleForm.querySelector('[name="discount"]').value) || 0;
        const tax = parseFloat(saleForm.querySelector('[name="tax"]')?.value) || 0;
        const points = parseFloat(saleForm.querySelector('[name="redeem_points"]')?.value) || 0;
        return { subtotal, total: Math.max(subtotal - discount + tax - points * pointValue, 0) };
    }

    function paidTotal() {
        return Array.from(paymentsBody.querySelectorAll('.payment-amount')).reduce((sum, i) => sum + (parseFloat(i.value) || 0), 0);
    }

    function recalcTotals() {
        const { subtotal, total } = grandTotal();
        const itemCount = itemRows().reduce((sum, row) => sum + (parseFloat(row.querySelector('.qty-input').value) || 0), 0);
        document.getElementById('summary-items').textContent = +itemCount.toFixed(3);
        document.getElementById('summary-subtotal').textContent = money(subtotal);
        document.getElementById('summary-grand').textContent = money(total);

        // The first payment line follows the grand total until the cashier types an amount into it.
        const firstPayment = paymentsBody.querySelector('.payment-amount');
        if (firstPayment && firstPayment.dataset.auto === '1') {
            firstPayment.value = total > 0 ? money(total) : '';
        }

        const paid = paidTotal();
        const received = parseFloat(document.getElementById('cash-received').value) || 0;
        const box = document.getElementById('balance-box');
        const label = document.getElementById('balance-label');
        const amount = document.getElementById('balance-amount');
        box.className = 'pos-balance mt-2 d-flex justify-content-between ';

        if (paid > total + 0.0001) {
            box.className += 'bg-danger-subtle text-danger-emphasis';
            label.textContent = posText.overpaid;
            amount.textContent = money(paid - total);
        } else if (received > 0 && received >= total && total > 0) {
            box.className += 'bg-success-subtle text-success-emphasis';
            label.textContent = posText.change;
            amount.textContent = money(received - total);
        } else if (total - paid > 0.0001) {
            box.className += 'bg-warning-subtle text-warning-emphasis';
            label.textContent = posText.due;
            amount.textContent = money(total - paid);
        } else {
            box.className += 'bg-success-subtle text-success-emphasis';
            label.textContent = posText.paid;
            amount.textContent = money(0);
        }
    }

    function buildPaymentRow(index) {
        const row = document.createElement('div');
        row.className = 'input-group input-group-sm mb-1 payment-row';
        row.innerHTML = `
            <select name="payments[${index}][payment_method]" class="form-select" style="max-width:42%">
                <option value="cash">${posText.cash}</option>
                <option value="card">${posText.card}</option>
                <option value="mobile_banking">${posText.mobileBanking}</option>
                <option value="bank">${posText.bank}</option>
                <option value="other">${posText.other}</option>
            </select>
            <input type="number" step="0.01" min="0" name="payments[${index}][amount]" class="form-control payment-amount" placeholder="0.00">
            <input type="text" name="payments[${index}][reference]" class="form-control d-none payment-reference" placeholder="${posText.reference}">
            <button type="button" class="btn btn-outline-danger remove-payment-row" title="${posText.remove}"><i class="fas fa-times"></i></button>
        `;
        return row;
    }

    document.getElementById('add-payment-row').addEventListener('click', function () {
        paymentsBody.appendChild(buildPaymentRow(paymentIndex++));
    });

    // "Exact": the first payment line takes whatever is still unpaid.
    document.getElementById('pay-exact').addEventListener('click', function () {
        const first = paymentsBody.querySelector('.payment-amount');
        if (!first) return;
        const others = paidTotal() - (parseFloat(first.value) || 0);
        first.value = money(Math.max(grandTotal().total - others, 0));
        first.dataset.auto = others > 0 ? '0' : '1';
        recalcTotals();
    });

    paymentsBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('payment-amount')) e.target.dataset.auto = '0';
        recalcTotals();
    });
    paymentsBody.addEventListener('change', function (e) {
        if (e.target.matches('select[name$="[payment_method]"]')) {
            // Card, mobile banking and bank payments usually carry a transaction reference.
            e.target.closest('.payment-row').querySelector('.payment-reference').classList.toggle('d-none', e.target.value === 'cash');
        }
    });
    paymentsBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-payment-row')) {
            e.target.closest('.payment-row').remove();
            recalcTotals();
        }
    });

    // When the cash received covers the bill, the cash payment line is set to the bill (not the cash handed over).
    document.getElementById('cash-received').addEventListener('input', function () {
        const first = paymentsBody.querySelector('.payment-amount');
        const received = parseFloat(this.value) || 0;
        if (first && received > 0) {
            first.value = money(Math.min(received, grandTotal().total));
            first.dataset.auto = '0';
        }
        recalcTotals();
    });

    saleForm.querySelectorAll('.pos-total-input').forEach(input => input.addEventListener('input', recalcTotals));
    document.getElementById('customer-select').addEventListener('change', refreshCustomerInfo);

    saleForm.addEventListener('submit', function (e) {
        if (!itemRows().length) {
            e.preventDefault();
            showScanFeedback(posText.emptyCart, false);
            focusSearch();
            return;
        }
        // Blank payment lines (e.g. a credit sale) are not sent.
        paymentsBody.querySelectorAll('.payment-row').forEach(row => {
            if (!(parseFloat(row.querySelector('.payment-amount').value) > 0)) {
                row.querySelectorAll('select, input').forEach(field => field.disabled = true);
            }
        });
    });

    // ---------- Keyboard shortcuts ----------
    document.addEventListener('keydown', function (e) {
        if (e.key === 'F2') {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        } else if (e.key === 'F9') {
            e.preventDefault();
            saleForm.requestSubmit(document.getElementById('complete-sale'));
        }
    });

    // ---------- Start ----------
    paymentsBody.appendChild(buildPaymentRow(paymentIndex++));
    paymentsBody.querySelector('.payment-amount').dataset.auto = '1';
    renderGrid();
    refreshCustomerInfo();

    // Resuming a held sale: refill the header fields and rebuild each line.
    const resumedSale = @json($resumedPayload);
    if (resumedSale) {
        ['customer_id', 'store_id', 'warehouse_id', 'sale_date', 'discount', 'tax', 'coupon_code', 'notes'].forEach(name => {
            const field = saleForm.querySelector(`[name="${name}"]`);
            if (field && resumedSale[name] !== undefined && resumedSale[name] !== null) field.value = resumedSale[name];
        });

        (resumedSale.items || []).forEach(item => {
            const product = retailProducts.find(p => p.id == item.product_id);
            if (!product) return;

            const row = addProductRow(product);
            if (item.unit_id) row.querySelector('.unit-select').value = item.unit_id;
            row.querySelector('.qty-input').value = item.quantity ?? 0;
            if (item.sale_price !== undefined && item.sale_price !== null) row.querySelector('.price-input').value = item.sale_price;
            row.querySelector('.discount-input').value = item.discount ?? 0;
            if (row.querySelector('.tax-input')) row.querySelector('.tax-input').value = item.tax ?? 0;

            if (item.batch_id && batchSelectFor(row)) batchSelectFor(row).value = item.batch_id;

            const serialSelect = trackingRowFor(row).querySelector('.serial-select');
            if (serialSelect && Array.isArray(item.serial_ids)) {
                const wanted = item.serial_ids.map(String);
                Array.from(serialSelect.options).forEach(o => { o.selected = wanted.includes(o.value); });
                syncSerialSelection(row);
            }

            recalculateRow(row);
        });
        refreshCustomerInfo();
        renderGrid();
    }

    updateCartState();
</script>
@endpush
