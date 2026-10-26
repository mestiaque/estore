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
            @feature('barcode')
            <div class="row mb-2">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                        <input type="text" id="barcode-scan" class="form-control" autocomplete="off" autofocus
                               placeholder="@lang('Scan product / lot barcode, or type SKU and press Enter')">
                    </div>
                    <small id="barcode-scan-feedback" class="d-block"></small>
                </div>
            </div>
            @endfeature
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex" id="items-table">
                    <thead class="text-center">
                        <tr>
                            <th style="min-width:220px">@lang('Product')</th>
                            <th style="min-width:130px">@lang('Unit')</th>
                            <th style="min-width:100px">@lang('Quantity')</th>
                            <th style="min-width:110px">@lang('Price')</th>
                            <th style="min-width:100px">@lang('Discount')</th>
                            @feature('tax')<th style="min-width:100px">@lang('Tax')</th>@endfeature
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
                    @feature('tax')
                    <div class="mb-2 d-flex justify-content-between">
                        <label class="form-label">@lang('Invoice Tax')</label>
                        <input type="number" step="0.01" name="tax" class="form-control form-control-sm w-50" value="{{ old('tax', 0) }}">
                    </div>
                    @endfeature
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
                    @if(\ME\Retail\Support\RetailModules::enabled('cash_register') && $openSessions->isNotEmpty())
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
            @feature('tax')<td><input type="number" step="0.01" name="items[${index}][tax]" class="form-control form-control-sm tax-input" value="0"></td>@endfeature
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
        row.innerHTML = `<td colspan="${document.querySelectorAll('#items-table thead th').length}" class="bg-light tracking-cell"></td>`;
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
                        <input type="text" name="items[${index}][batch_number]" class="form-control form-control-sm batch-number-input" required>
                    </div>
                    ${product.has_expiry ? `
                    <div class="col-md-4">
                        <label class="form-label mb-0">@lang('Expiry Date')</label>
                        <input type="date" name="items[${index}][expiry_date]" class="form-control form-control-sm expiry-input" required>
                    </div>` : ''}
                    @feature('barcode')
                    <div class="col-md-4">
                        <label class="form-label mb-0">@lang('Lot Barcode')</label>
                        <input type="text" name="items[${index}][batch_barcode]" class="form-control form-control-sm batch-barcode-input" placeholder="@lang('Optional: scan the supplier lot barcode')">
                    </div>
                    @endfeature
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
                    @feature('warranty')
                    <div class="col-md-4">
                        <label class="form-label mb-0">@lang('Warranty (months)')</label>
                        <input type="number" min="0" step="1" name="items[${index}][warranty_months]" class="form-control form-control-sm" placeholder="@lang('Leave blank for no warranty')">
                    </div>
                    @endfeature
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

        // Scanning serials/IMEIs one per line makes the line's quantity the serial count.
        const itemRow = itemsBody.querySelector(`tr:not(.tracking-row)[data-index="${index}"]`);
        itemRow.querySelector('.qty-input').value = values.length;
        recalculateRow(itemRow);
    }

    function recalculateRow(row) {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const discount = parseFloat(row.querySelector('.discount-input').value) || 0;
        const tax = parseFloat(row.querySelector('.tax-input')?.value) || 0;
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

    @feature('barcode')
    // Barcode scanning — scanners type the code and press Enter. The server resolves the code
    // (product barcode, lot barcode or SKU); a repeat scan of the same item adds 1.
    const barcodeLookupUrl = @json(route('retail.barcode.lookup'));
    const productCreateUrl = @json(route('retail.products.create'));
    const scanInput = document.getElementById('barcode-scan');
    const scanFeedback = document.getElementById('barcode-scan-feedback');

    function showScanFeedback(message, isSuccess, code = null) {
        scanFeedback.className = 'd-block ' + (isSuccess ? 'text-success' : 'text-danger');
        scanFeedback.textContent = message;
        if (code !== null) {
            const link = document.createElement('a');
            link.href = `${productCreateUrl}?barcode=${encodeURIComponent(code)}`;
            link.target = '_blank';
            link.className = 'ms-2';
            link.textContent = @json(__('Create product with this barcode'));
            scanFeedback.appendChild(link);
        }
    }

    function itemRows() {
        return Array.from(itemsBody.querySelectorAll('tr:not(.tracking-row)'));
    }

    function trackingRowFor(row) {
        return itemsBody.querySelector(`tr.tracking-row[data-index="${row.dataset.index}"]`);
    }

    function rowsOfProduct(product) {
        return itemRows().filter(row =>
            row.querySelector('.product-select').value == product.id
            && row.querySelector('.unit-select').value == product.unit_id
        );
    }

    function rowForNewProduct(product) {
        let row = itemRows().find(r => !r.querySelector('.product-select').value);
        if (!row) {
            document.getElementById('add-item-row').click();
            row = itemRows().at(-1);
        }
        row.querySelector('.product-select').value = product.id;
        refreshUnitOptions(row);
        row.querySelector('.unit-select').value = product.unit_id;
        return row;
    }

    function addOne(row) {
        const qtyInput = row.querySelector('.qty-input');
        qtyInput.value = (parseFloat(qtyInput.value) || 0) + 1;
        recalculateRow(row);
    }

    async function handleScan(code) {
        let data = null;
        let ok = false;
        try {
            const params = new URLSearchParams({ code, context: 'purchase' });
            const warehouse = document.querySelector('[name="warehouse_id"]')?.value;
            if (warehouse) params.set('warehouse_id', warehouse);
            const response = await fetch(`${barcodeLookupUrl}?${params}`, { headers: { Accept: 'application/json' } });
            ok = response.ok;
            data = await response.json();
        } catch (e) {
            data = null;
        }

        if (!ok || !data?.found) {
            showScanFeedback(data?.message ?? @json(__('Barcode lookup failed.')), false, ok || data ? code : null);
            return;
        }

        const product = retailProducts.find(p => p.id == data.product.id);
        if (!product) {
            showScanFeedback(@json(__('This product is not available for purchase.')), false);
            return;
        }

        if (product.track_type === 'serial') {
            const row = rowsOfProduct(product)[0] || rowForNewProduct(product);
            trackingRowFor(row).querySelector('.serial-textarea')?.focus();
            showScanFeedback(`${product.name}: ` + @json(__('now scan each serial / IMEI into the serial box.')), true);
            return;
        }

        if (product.track_type === 'batch') {
            if (data.batch) {
                // A lot barcode: add to that lot's line, or start a line pre-filled with the lot.
                let row = rowsOfProduct(product).find(r => trackingRowFor(r).querySelector('.batch-number-input')?.value === data.batch.batch_number);
                if (!row) {
                    row = rowForNewProduct(product);
                    const tracking = trackingRowFor(row);
                    tracking.querySelector('.batch-number-input').value = data.batch.batch_number;
                    tracking.querySelector('.batch-barcode-input').value = data.batch.barcode ?? '';
                    const expiry = tracking.querySelector('.expiry-input');
                    if (expiry && data.batch.expiry_date) expiry.value = data.batch.expiry_date;
                }
                addOne(row);
                showScanFeedback(`${product.name} — ` + @json(__('Batch')) + ` ${data.batch.batch_number}`, true);
                return;
            }

            // A product barcode on a batch product: keep adding to the latest line, whose lot was typed in.
            const row = rowsOfProduct(product).at(-1) || rowForNewProduct(product);
            addOne(row);
            const batchInput = trackingRowFor(row).querySelector('.batch-number-input');
            if (!batchInput.value) batchInput.focus();
            showScanFeedback(`${product.name}: ` + @json(__('enter the batch number for this line.')), true);
            return;
        }

        addOne(rowsOfProduct(product)[0] || rowForNewProduct(product));
        showScanFeedback(product.name, true);
    }

    scanInput.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const code = scanInput.value.trim();
        scanInput.value = '';
        if (code !== '') handleScan(code);
    });
    @endfeature

    // A scanner's trailing Enter inside an item field (e.g. a lot barcode) must not submit the purchase.
    itemsBody.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && e.target.tagName === 'INPUT') e.preventDefault();
    });
</script>
@endpush
