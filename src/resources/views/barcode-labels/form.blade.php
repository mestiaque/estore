@extends('me::master')

@section('title', trans('Print Barcode Labels'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.barcode-labels.print') }}" method="POST" target="_blank">
            @csrf
            <div class="row mb-3">
                <div class="col-md-4">
                    <label class="form-label">@lang('Label Size')</label>
                    <select name="size" id="size-select" class="form-control" required>
                        @foreach($sizes as $key => $dimension)
                            <option value="{{ $key }}">{{ $key }} mm</option>
                        @endforeach
                        <option value="custom">@lang('Custom')</option>
                    </select>
                </div>
                <div class="col-md-4" id="custom-width-field" style="display:none">
                    <label class="form-label">@lang('Width (mm)')</label>
                    <input type="number" step="0.1" min="10" name="custom_width" class="form-control">
                </div>
                <div class="col-md-4" id="custom-height-field" style="display:none">
                    <label class="form-label">@lang('Height (mm)')</label>
                    <input type="number" step="0.1" min="10" name="custom_height" class="form-control">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th></th>
                            <th>@lang('Product')</th>
                            <th>@lang('Barcode')</th>
                            <th style="width:120px">@lang('Quantity')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $index => $product)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="product-check" data-index="{{ $index }}">
                                </td>
                                <td>{{ $product->name }} ({{ $product->sku }})</td>
                                <td>{{ $product->barcode ?? __('N/A (generate one first)') }}</td>
                                <td>
                                    <input type="hidden" name="products[{{ $index }}][id]" value="{{ $product->id }}" disabled class="qty-id-input">
                                    <input type="number" min="1" value="1" name="products[{{ $index }}][quantity]" class="form-control form-control-sm qty-input" disabled>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($batches->isNotEmpty())
            <h6 class="mt-3">@lang('Batch / Lot Labels')</h6>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th></th>
                            <th>@lang('Product')</th>
                            <th>@lang('Batch')</th>
                            <th>@lang('Expiry')</th>
                            <th>@lang('Warehouse')</th>
                            <th>@lang('Barcode')</th>
                            <th style="width:120px">@lang('Quantity')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $index => $batch)
                            <tr>
                                <td class="text-center">
                                    <input type="checkbox" class="product-check">
                                </td>
                                <td>{{ $batch->product->name ?? '' }}</td>
                                <td>{{ $batch->batch_number }}</td>
                                <td>{{ $batch->expiry_date?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $batch->warehouse->name ?? '' }}</td>
                                <td>{{ $batch->barcode ?: __('N/A (uses product barcode)') }}</td>
                                <td>
                                    <input type="hidden" name="batches[{{ $index }}][id]" value="{{ $batch->id }}" disabled class="qty-id-input">
                                    <input type="number" min="1" value="1" name="batches[{{ $index }}][quantity]" class="form-control form-control-sm qty-input" disabled>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @component('me::components.btn.save-button', ['text' => __('Print Labels')])
            @endcomponent
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    document.getElementById('size-select').addEventListener('change', function () {
        const isCustom = this.value === 'custom';
        document.getElementById('custom-width-field').style.display = isCustom ? '' : 'none';
        document.getElementById('custom-height-field').style.display = isCustom ? '' : 'none';
    });

    document.querySelectorAll('.product-check').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const row = this.closest('tr');
            row.querySelectorAll('.qty-id-input, .qty-input').forEach(function (input) {
                input.disabled = !checkbox.checked;
            });
        });
    });
</script>
@endpush
