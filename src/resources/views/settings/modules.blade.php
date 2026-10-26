@extends('me::master')

@section('title', trans('Module Settings'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Optional Modules')</b></div>
    <div class="card-body">
        <p class="text-muted mb-3">
            @lang('Switch off any module this business does not use. Turned-off modules disappear from the sidebar; nothing already saved is deleted.')
        </p>
        <form action="{{ route('retail.settings.modules.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="enable_discount" name="enable_discount" value="1" {{ $discountEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="enable_discount">
                    <b>@lang('Discount Rules')</b><br>
                    <small class="text-muted">@lang('Product, category, brand, quantity-break, and coupon discounts.')</small>
                </label>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="enable_loyalty" name="enable_loyalty" value="1" {{ $loyaltyEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="enable_loyalty">
                    <b>@lang('Loyalty Points')</b><br>
                    <small class="text-muted">@lang('Customer levels, points earning/redemption, and the Loyalty settings/report.')</small>
                </label>
            </div>

            <div class="form-check form-switch mb-3">
                <input class="form-check-input" type="checkbox" role="switch" id="enable_emi" name="enable_emi" value="1" {{ $emiEnabled ? 'checked' : '' }}>
                <label class="form-check-label" for="enable_emi">
                    <b>@lang('EMI')</b><br>
                    <small class="text-muted">@lang('Financing a sale\'s due amount through an EMI bank/plan/contract.')</small>
                </label>
            </div>

            @component('me::components.btn.save-button', ['text' => __('Save Settings')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
