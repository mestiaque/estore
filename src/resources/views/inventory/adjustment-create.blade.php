@extends('me::master')

@section('title', trans('Adjust Stock'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.stock-adjustments.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Product')</label>
                    <select name="product_id" class="form-control" required>
                        <option value="">@lang('Select Product')</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} ({{ $product->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    @include('retail::partials._warehouse-select', ['warehouses' => $warehouses])
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Adjustment Quantity')</label>
                    <input type="number" step="0.0001" name="quantity_adjusted" class="form-control" value="{{ old('quantity_adjusted') }}" required>
                    <small class="text-muted">@lang('Use a negative number to decrease stock, positive to increase.')</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Reason')</label>
                    <select name="reason" class="form-control" required>
                        <option value="">@lang('Select Reason')</option>
                        @foreach($reasons as $reason)
                            <option value="{{ $reason->value }}" {{ old('reason') == $reason->value ? 'selected' : '' }}>
                                {{ $reason->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">@lang('Notes')</label>
                    <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Save Adjustment')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
