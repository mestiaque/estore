@extends('me::master')

@section('title', trans('New Sale'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.sales.store') }}" method="POST" id="sale-form">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('Customer')</label>
                    <select name="customer_id" id="customer-select" class="form-control">
                        <option value="">@lang('Walk-in Customer')</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }} ({{ number_format($customer->points_balance, 0) }} pts)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    @include('retail::partials._store-select', ['stores' => $stores])
                </div>
                <div class="col-md-4 mb-3">
                    @include('retail::partials._warehouse-select', ['warehouses' => $warehouses])
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('Sale Date')</label>
                    <input type="date" name="sale_date" class="form-control" value="{{ old('sale_date', date('Y-m-d')) }}" required>
                </div>
            </div>

            <hr>
            <h6>@lang('Items')</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex" id="items-table">
                    <thead class="text-center">
                        <tr>
                            <th style="min-width:220px">@lang('Product')</th>
                            <th style="min-width:130px">@lang('Unit')</th>
                            <th style="min-width:100px">@lang('Quantity')</th>
                            <th style="min-width:110px">@lang('Price')</th>
                            <th style="min-width:100px">@lang('Discount')</th>
                            <th style="min-width:100px">@lang('Tax')</th>
                            <th>@lang('Total')</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>
            </div>
            <button type="button" id="add-item-row" class="btn btn-sm btn-encodex-list mb-3">
                <i class="fas fa-plus"></i> @lang('Add Item')
            </button>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Invoice Discount')</label>
                        <input type="number" step="0.01" name="discount" class="form-control form-control-sm w-50" value="{{ old('discount', 0) }}">
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Invoice Tax')</label>
                        <input type="number" step="0.01" name="tax" class="form-control form-control-sm w-50" value="{{ old('tax', 0) }}">
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Redeem Points')</label>
                        <input type="number" step="0.01" min="0" name="redeem_points" id="redeem-points-input" class="form-control form-control-sm w-50" value="{{ old('redeem_points', 0) }}">
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Coupon Code')</label>
                        <input type="text" name="coupon_code" class="form-control form-control-sm w-50" value="{{ old('coupon_code') }}">
                    </div>
                    <small class="text-muted d-block mb-3" id="points-available-hint"></small>
                    <div class="mb-3">
                        <label class="form-label">@lang('Notes')</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="col-md-6">
                    <h6>@lang('Payments')</h6>
                    @if($openSessions->isNotEmpty())
                    <div class="mb-2">
                        <label class="form-label">@lang('Cash Register Session')</label>
                        <select name="cash_register_session_id" class="form-control form-control-sm">
                            <option value="">@lang('None (cash payments will not affect a drawer)')</option>
                            @foreach($openSessions as $session)
                                <option value="{{ $session->id }}" {{ old('cash_register_session_id') == $session->id ? 'selected' : '' }}>
                                    {{ $session->cashRegister->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-encodex" id="payments-table">
                            <thead class="text-center">
                                <tr>
                                    <th>@lang('Method')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Reference')</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="payments-body"></tbody>
                        </table>
                    </div>
                    <button type="button" id="add-payment-row" class="btn btn-sm btn-encodex-list mb-3">
                        <i class="fas fa-plus"></i> @lang('Add Payment')
                    </button>
                    <small class="text-muted d-block">@lang('Leave payments empty for a fully due credit sale (requires a registered customer). A walk-in sale must be paid in full.')</small>
                </div>
            </div>

            @component('me::components.btn.save-button', ['text' => __('Complete Sale')])
            @endcomponent
        </form>
    </div>
</div>
@endsection

@php
    // Computed into plain variables first — Blade's @json() splits its argument on every
    // top-level comma, so an inline multi-key array literal must never be passed directly.
    $retailProductsData = $products->map(fn($p) => [
        'id' => $p->id, 'name' => $p->name, 'sku' => $p->sku,
        'unit_id' => $p->unit_id, 'unit_name' => $p->unit->name ?? '',
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
    ])->sortBy('expiry_date')->values();
    $retailSerialsData = $serials->map(fn($s) => [
        'id' => $s->id, 'product_id' => $s->product_id, 'warehouse_id' => $s->warehouse_id,
        'serial_number' => $s->serial_number, 'warranty_end' => $s->warranty_end?->format('Y-m-d'),
    ]);
    $retailCustomersData = $customers->map(fn($c) => [
        'id' => $c->id, 'points_balance' => (float) $c->points_balance,
    ]);
@endphp
@push('js')
<script>
    const retailProducts = @json($retailProductsData);
    const retailUnits = @json($retailUnitsData);
    const retailBatches = @json($retailBatchesData);
    const retailSerials = @json($retailSerialsData);
    const retailCustomers = @json($retailCustomersData);

    function warehouseId() {
        return document.querySelector('[name="warehouse_id"]').value;
    }

    function refreshPointsHint() {
        const customerId = document.getElementById('customer-select').value;
        const hint = document.getElementById('points-available-hint');
        const customer = retailCustomers.find(c => c.id == customerId);
        hint.textContent = customer ? `@lang('Available points'): ${customer.points_balance}` : '';
    }

    document.getElementById('customer-select').addEventListener('change', refreshPointsHint);
    refreshPointsHint();

    function unitsForProduct(productId) {
        const product = retailProducts.find(p => p.id == productId);
        if (!product) return [];
        return retailUnits.filter(u => u.id == product.unit_id || u.base_unit_id == product.unit_id);
    }

    function buildItemRow(index) {
        const row = document.createElement('tr');
        row.dataset.index = index;
        row.innerHTML = `
            <td>
                <select name="items[${index}][product_id]" class="form-control form-control-sm product-select" required>
                    <option value="">@lang('Select Product')</option>
                    ${retailProducts.map(p => `<option value="${p.id}">${p.name} (${p.sku})</option>`).join('')}
                </select>
            </td>
            <td><select name="items[${index}][unit_id]" class="form-control form-control-sm unit-select" required></select></td>
            <td><input type="number" step="0.0001" name="items[${index}][quantity]" class="form-control form-control-sm qty-input" required></td>
            <td><input type="number" step="0.01" name="items[${index}][sale_price]" class="form-control form-control-sm price-input" required></td>
            <td><input type="number" step="0.01" name="items[${index}][discount]" class="form-control form-control-sm discount-input" value="0"></td>
            <td><input type="number" step="0.01" name="items[${index}][tax]" class="form-control form-control-sm tax-input" value="0"></td>
            <td class="row-total align-middle text-end">0.00</td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-encodex-delete remove-row" title="@lang('Remove')"><i class="fas fa-trash"></i></button>
            </td>
        `;
        return row;
    }

    function buildTrackingRow(index) {
        const row = document.createElement('tr');
        row.classList.add('tracking-row');
        row.dataset.index = index;
        row.style.display = 'none';
        row.innerHTML = `<td colspan="8" class="bg-light tracking-cell"></td>`;
        return row;
    }

    function refreshUnitOptions(row) {
        const productId = row.querySelector('.product-select').value;
        const unitSelect = row.querySelector('.unit-select');
        const options = unitsForProduct(productId);
        unitSelect.innerHTML = options.map(u => `<option value="${u.id}">${u.name}</option>`).join('');

        const product = retailProducts.find(p => p.id == productId);
        if (product) {
            row.querySelector('.price-input').value = product.sale_price;
        }

        refreshTrackingRow(row, product);
    }

    function refreshTrackingRow(row, product) {
        const index = row.dataset.index;
        const trackingRow = itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`);
        const cell = trackingRow.querySelector('.tracking-cell');
        const wid = warehouseId();

        if (!product || product.track_type === 'normal') {
            trackingRow.style.display = 'none';
            cell.innerHTML = '';
            return;
        }

        trackingRow.style.display = '';

        if (product.track_type === 'batch') {
            const options = retailBatches.filter(b => b.product_id == product.id && (!wid || b.warehouse_id == wid) && b.quantity > 0);
            cell.innerHTML = `
                <label class="form-label mb-0">@lang('Batch (earliest expiry first)')</label>
                <select name="items[${index}][batch_id]" class="form-control form-control-sm" required>
                    <option value="">@lang('Select Batch')</option>
                    ${options.map(b => `<option value="${b.id}">${b.batch_number}${b.expiry_date ? ' — exp ' + b.expiry_date : ''} (${b.quantity} @lang('available'))</option>`).join('')}
                </select>
            `;
        } else if (product.track_type === 'serial') {
            const options = retailSerials.filter(s => s.product_id == product.id && (!wid || s.warehouse_id == wid));
            cell.innerHTML = `
                <label class="form-label mb-0">@lang('Select Serials (selected count becomes the quantity)')</label>
                <select class="form-control form-control-sm serial-select" multiple size="4">
                    ${options.map(s => `<option value="${s.id}">${s.serial_number}${s.warranty_end ? ' — @lang('warranty until') ' + s.warranty_end : ' — @lang('no warranty')'}</option>`).join('')}
                </select>
                <div class="serial-inputs"></div>
                <small class="text-muted d-block serial-warranty-hint"></small>
            `;
        }
    }

    function syncSerialSelection(row) {
        const index = row.dataset.index;
        const trackingRow = itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`);
        const select = trackingRow?.querySelector('.serial-select');
        if (!select) return;

        const selected = Array.from(select.selectedOptions).map(o => o.value);
        trackingRow.querySelector('.serial-inputs').innerHTML =
            selected.map(id => `<input type="hidden" name="items[${index}][serial_ids][]" value="${id}">`).join('');

        const selectedSerials = retailSerials.filter(s => selected.includes(String(s.id)));
        const hint = trackingRow.querySelector('.serial-warranty-hint');
        if (hint) {
            hint.textContent = selectedSerials.length
                ? selectedSerials.map(s => `${s.serial_number}: ${s.warranty_end ? '@lang('warranty until') ' + s.warranty_end : '@lang('no warranty')'}`).join(' | ')
                : '';
        }

        row.querySelector('.qty-input').value = selected.length;
        recalculateRow(row);
    }

    function recalculateRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
        const total = (qty * price) - discount + tax;
        row.querySelector('.row-total').textContent = total.toFixed(2);
    }

    let itemIndex = 0;
    const itemsBody = document.getElementById('items-body');

    document.getElementById('add-item-row').addEventListener('click', function () {
        const index = itemIndex++;
        itemsBody.appendChild(buildItemRow(index));
        itemsBody.appendChild(buildTrackingRow(index));
    });

    itemsBody.addEventListener('change', function (e) {
        if (e.target.classList.contains('serial-select')) {
            syncSerialSelection(e.target.closest('tr.tracking-row').previousElementSibling);
            return;
        }
        const row = e.target.closest('tr');
        if (!row || row.classList.contains('tracking-row')) return;
        if (e.target.classList.contains('product-select')) {
            refreshUnitOptions(row);
        }
        recalculateRow(row);
    });

    itemsBody.addEventListener('input', function (e) {
        const row = e.target.closest('tr');
        if (row && !row.classList.contains('tracking-row')) {
            recalculateRow(row);
        }
    });

    itemsBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('tr');
            const index = row.dataset.index;
            itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`)?.remove();
            row.remove();
        }
    });

    document.querySelector('[name="warehouse_id"]').addEventListener('change', function () {
        itemsBody.querySelectorAll('tr:not(.tracking-row)').forEach(row => {
            const productId = row.querySelector('.product-select')?.value;
            const product = retailProducts.find(p => p.id == productId);
            if (product) refreshTrackingRow(row, product);
        });
    });

    document.getElementById('add-item-row').click();

    // Payments (mixed payment support)
    let paymentIndex = 0;
    const paymentsBody = document.getElementById('payments-body');

    function buildPaymentRow(index) {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <select name="payments[${index}][payment_method]" class="form-control form-control-sm">
                    <option value="cash">@lang('Cash')</option>
                    <option value="bank">@lang('Bank')</option>
                    <option value="card">@lang('Card')</option>
                    <option value="mobile_banking">@lang('Mobile Banking')</option>
                    <option value="other">@lang('Other')</option>
                </select>
            </td>
            <td><input type="number" step="0.01" name="payments[${index}][amount]" class="form-control form-control-sm"></td>
            <td><input type="text" name="payments[${index}][reference]" class="form-control form-control-sm"></td>
            <td class="text-center align-middle">
                <button type="button" class="btn btn-sm btn-encodex-delete remove-payment-row" title="@lang('Remove')"><i class="fas fa-trash"></i></button>
            </td>
        `;
        return row;
    }

    document.getElementById('add-payment-row').addEventListener('click', function () {
        paymentsBody.appendChild(buildPaymentRow(paymentIndex++));
    });

    paymentsBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-payment-row')) {
            e.target.closest('tr').remove();
        }
    });

    document.getElementById('add-payment-row').click();
</script>
@endpush
