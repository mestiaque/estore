@extends('me::master')

@section('title', trans('New Stock Transfer'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.stock-transfers.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('From Warehouse')</label>
                    <select name="from_warehouse_id" id="from-warehouse-select" class="form-control" required>
                        <option value="">@lang('Select Warehouse')</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('from_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('To Warehouse')</label>
                    <select name="to_warehouse_id" class="form-control" required>
                        <option value="">@lang('Select Warehouse')</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('to_warehouse_id') == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">@lang('Transfer Date')</label>
                    <input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', date('Y-m-d')) }}" required>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="items-body"></tbody>
                </table>
            </div>
            <button type="button" id="add-item-row" class="btn btn-sm btn-encodex-list mb-3">
                <i class="fas fa-plus"></i> @lang('Add Item')
            </button>

            <div class="mb-3">
                <label class="form-label">@lang('Notes')</label>
                <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
            </div>

            @component('me::components.btn.save-button', ['text' => __('Save Transfer')])
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
        'unit_id' => $p->unit_id, 'track_type' => $p->track_type,
    ]);
    $retailUnitsData = $units->map(fn($u) => [
        'id' => $u->id, 'name' => $u->name, 'base_unit_id' => $u->base_unit_id,
    ]);
    $retailBatchesData = $batches->map(fn($b) => [
        'id' => $b->id, 'product_id' => $b->product_id, 'warehouse_id' => $b->warehouse_id,
        'batch_number' => $b->batch_number, 'expiry_date' => $b->expiry_date?->format('Y-m-d'),
        'quantity' => (float) $b->quantity,
    ]);
    $retailSerialsData = $serials->map(fn($s) => [
        'id' => $s->id, 'product_id' => $s->product_id, 'warehouse_id' => $s->warehouse_id,
        'serial_number' => $s->serial_number,
    ]);
@endphp
@push('js')
<script>
    const retailProducts = @json($retailProductsData);
    const retailUnits = @json($retailUnitsData);
    const retailBatches = @json($retailBatchesData);
    const retailSerials = @json($retailSerialsData);

    function fromWarehouseId() {
        return document.getElementById('from-warehouse-select').value;
    }

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
        row.innerHTML = `<td colspan="4" class="bg-light tracking-cell"></td>`;
        return row;
    }

    function refreshUnitOptions(row) {
        const productId = row.querySelector('.product-select').value;
        const unitSelect = row.querySelector('.unit-select');
        unitSelect.innerHTML = unitsForProduct(productId).map(u => `<option value="${u.id}">${u.name}</option>`).join('');
        refreshTrackingRow(row);
    }

    function refreshTrackingRow(row) {
        const index = row.dataset.index;
        const productId = row.querySelector('.product-select').value;
        const product = retailProducts.find(p => p.id == productId);
        const trackingRow = itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`);
        const cell = trackingRow.querySelector('.tracking-cell');
        const wid = fromWarehouseId();

        if (!product || product.track_type === 'normal') {
            trackingRow.style.display = 'none';
            cell.innerHTML = '';
            return;
        }

        trackingRow.style.display = '';

        if (product.track_type === 'batch') {
            const options = retailBatches.filter(b => b.product_id == product.id && (!wid || b.warehouse_id == wid) && b.quantity > 0);
            cell.innerHTML = `
                <label class="form-label mb-0">@lang('Source Batch')</label>
                <select name="items[${index}][source_batch_id]" class="form-control form-control-sm" required>
                    <option value="">@lang('Select Batch')</option>
                    ${options.map(b => `<option value="${b.id}">${b.batch_number}${b.expiry_date ? ' — exp ' + b.expiry_date : ''} (${b.quantity})</option>`).join('')}
                </select>
            `;
        } else if (product.track_type === 'serial') {
            const options = retailSerials.filter(s => s.product_id == product.id && (!wid || s.warehouse_id == wid));
            cell.innerHTML = `
                <label class="form-label mb-0">@lang('Select Serials (selected count becomes the quantity)')</label>
                <select class="form-control form-control-sm serial-select" multiple size="4">
                    ${options.map(s => `<option value="${s.id}">${s.serial_number}</option>`).join('')}
                </select>
                <div class="serial-inputs"></div>
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

        row.querySelector('.qty-input').value = selected.length;
    }

    let rowIndex = 0;
    const itemsBody = document.getElementById('items-body');

    document.getElementById('add-item-row').addEventListener('click', function () {
        const index = rowIndex++;
        itemsBody.appendChild(buildRow(index));
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
    });

    itemsBody.addEventListener('click', function (e) {
        if (e.target.closest('.remove-row')) {
            const row = e.target.closest('tr');
            const index = row.dataset.index;
            itemsBody.querySelector(`tr.tracking-row[data-index="${index}"]`)?.remove();
            row.remove();
        }
    });

    document.getElementById('from-warehouse-select').addEventListener('change', function () {
        itemsBody.querySelectorAll('tr:not(.tracking-row)').forEach(row => refreshTrackingRow(row));
    });

    document.getElementById('add-item-row').click();
</script>
@endpush
