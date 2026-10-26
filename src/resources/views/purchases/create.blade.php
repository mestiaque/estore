@extends('me::master')

@section('title', trans('Add Purchase'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.purchases.store') }}" method="POST" id="purchase-form">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('Supplier')</label>
                    <select name="supplier_id" class="form-control" required>
                        <option value="">@lang('Select Supplier')</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>{{ $supplier->name }}</option>
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
                    <label class="form-label">@lang('Purchase Date')</label>
                    <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('Reference Number')</label>
                    <input type="text" name="reference_number" class="form-control" value="{{ old('reference_number') }}">
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

            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Invoice Discount')</label>
                        <input type="number" step="0.01" name="discount" class="form-control form-control-sm w-50" value="{{ old('discount', 0) }}">
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Invoice Tax')</label>
                        <input type="number" step="0.01" name="tax" class="form-control form-control-sm w-50" value="{{ old('tax', 0) }}">
                    </div>
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Paid Amount')</label>
                        <input type="number" step="0.01" name="paid_amount" class="form-control form-control-sm w-50" value="{{ old('paid_amount', 0) }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">@lang('Payment Method')</label>
                        <select name="payment_method" class="form-control form-control-sm">
                            <option value="cash">@lang('Cash')</option>
                            <option value="bank">@lang('Bank')</option>
                            <option value="card">@lang('Card')</option>
                            <option value="mobile_banking">@lang('Mobile Banking')</option>
                            <option value="other">@lang('Other')</option>
                        </select>
                    </div>
                    @if($openSessions->isNotEmpty())
                    <div class="mb-2">
                        <label class="form-label">@lang('Cash Register Session')</label>
                        <select name="cash_register_session_id" class="form-control form-control-sm">
                            <option value="">@lang('None')</option>
                            @foreach($openSessions as $session)
                                <option value="{{ $session->id }}" {{ old('cash_register_session_id') == $session->id ? 'selected' : '' }}>
                                    {{ $session->cashRegister->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">@lang('Notes')</label>
                <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
            </div>

            @component('me::components.btn.save-button', ['text' => __('Save Purchase')])
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
        'purchase_price' => (float) $p->purchase_price,
        'track_type' => $p->track_type, 'has_expiry' => (bool) $p->has_expiry,
    ]);
    $retailUnitsData = $units->map(fn($u) => [
        'id' => $u->id, 'name' => $u->name, 'base_unit_id' => $u->base_unit_id,
    ]);
@endphp
@push('js')
<script>
    const retailProducts = @json($retailProductsData);
    const retailUnits = @json($retailUnitsData);

    function unitsForProduct(productId) {
        const product = retailProducts.find(p => p.id == productId);
        if (!product) return [];
        return retailUnits.filter(u => u.id == product.unit_id || u.base_unit_id == product.unit_id);
    }

    function buildRow(index) {
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
            <td><input type="number" step="0.01" name="items[${index}][purchase_price]" class="form-control form-control-sm price-input" required></td>
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
            row.querySelector('.price-input').value = product.purchase_price;
        }

        refreshTrackingRow(row, product);
    }

    function refreshTrackingRow(row, product) {
        const index = row.dataset.index;
        const trackingRow = itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`);
        const cell = trackingRow.querySelector('.tracking-cell');

        if (!product || product.track_type === 'normal') {
            trackingRow.style.display = 'none';
            cell.innerHTML = '';
            return;
        }

        trackingRow.style.display = '';

        if (product.track_type === 'batch') {
            cell.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-4">
                        <label class="form-label mb-0">@lang('Batch Number')</label>
                        <input type="text" name="items[${index}][batch_number]" class="form-control form-control-sm" required>
                    </div>
                    ${product.has_expiry ? `
                    <div class="col-md-4">
                        <label class="form-label mb-0">@lang('Expiry Date')</label>
                        <input type="date" name="items[${index}][expiry_date]" class="form-control form-control-sm" required>
                    </div>` : ''}
                </div>
            `;
        } else if (product.track_type === 'serial') {
            cell.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-8">
                        <label class="form-label mb-0">@lang('Serial Numbers (one per line, must match quantity)')</label>
                        <textarea class="form-control form-control-sm serial-textarea" rows="2"></textarea>
                        <div class="serial-inputs"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-0">@lang('Warranty (months)')</label>
                        <input type="number" min="0" step="1" name="items[${index}][warranty_months]" class="form-control form-control-sm" placeholder="@lang('Leave blank for no warranty')">
                    </div>
                </div>
            `;
        }
    }

    function syncSerialInputs(row) {
        const index = row.dataset.index;
        const trackingRow = itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`);
        const textarea = trackingRow?.querySelector('.serial-textarea');
        if (!textarea) return;

        const container = trackingRow.querySelector('.serial-inputs');
        const values = textarea.value.split('\n').map(v => v.trim()).filter(v => v !== '');
        container.innerHTML = values.map(v => `<input type="hidden" name="items[${index}][serial_numbers][]" value="${v.replace(/"/g, '&quot;')}">`).join('');
    }

    function recalculateRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input').value) || 0;
        const total = (qty * price) - discount + tax;
        row.querySelector('.row-total').textContent = total.toFixed(2);
    }

    let rowIndex = 0;
    const itemsBody = document.getElementById('items-body');

    document.getElementById('add-item-row').addEventListener('click', function () {
        const index = rowIndex++;
        itemsBody.appendChild(buildRow(index));
        itemsBody.appendChild(buildTrackingRow(index));
    });

    itemsBody.addEventListener('change', function (e) {
        const row = e.target.closest('tr.tracking-row') ? null : e.target.closest('tr');
        if (row && e.target.classList.contains('product-select')) {
            refreshUnitOptions(row);
        }
        if (row) {
            recalculateRow(row);
        }
    });

    itemsBody.addEventListener('input', function (e) {
        if (e.target.classList.contains('serial-textarea')) {
            syncSerialInputs(e.target.closest('tr.tracking-row'));
            return;
        }
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

    // Start with one empty row.
    document.getElementById('add-item-row').click();
</script>
@endpush
