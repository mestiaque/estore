@extends('me::master')

@section('title', trans('Loyalty Settings'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Loyalty Point Settings')</b></div>
    <div class="card-body">
        <form action="{{ route('retail.settings.loyalty.update') }}" method="POST">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">@lang('1 Point = How Many Taka')</label>
                <input type="number" step="0.0001" min="0.0001" name="point_value" class="form-control" value="{{ old('point_value', $pointValue) }}" required>
                <small class="text-muted">
                    @lang('Currency value of one loyalty point when redeemed. Example: 0.10 means 1 point = 0.10 Taka, so 10 points = 1 Taka.')
                </small>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('Maximum Redeemable Share of Invoice (%)')</label>
                <input type="number" step="1" min="0" max="100" name="max_redeem_percent" class="form-control" value="{{ old('max_redeem_percent', $maxRedeemPct) }}" required>
                <small class="text-muted">
                    @lang('The largest portion of a single sale\'s total that loyalty points may cover.')
                </small>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Save Settings')])
            @endcomponent
        </form>
    </div>
</div>

<div class="card glass-card w-100 mt-3">
    <div class="card-header"><b>@lang('Customer Levels (Points Required Per Level)')</b></div>
    <div class="card-body">
        <p class="text-muted mb-2">
            @lang('Each customer level\'s required points, discount, and point multiplier is managed from Customer Levels.')
        </p>
        @can('retail_customer_level.view')
        <a href="{{ route('retail.customer-levels.index') }}" class="btn btn-encodex-list">@lang('Manage Customer Levels')</a>
        @endcan
    </div>
</div>
@endsection
