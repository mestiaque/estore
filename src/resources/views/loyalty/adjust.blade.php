@extends('me::master')

@section('title', trans('Adjust Loyalty Points'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.loyalty.adjust.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">@lang('Customer')</label>
                <select name="customer_id" class="form-control" required>
                    <option value="">@lang('Select Customer')</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ number_format($customer->points_balance, 2) }} pts)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('Points')</label>
                <input type="number" step="0.01" name="points" class="form-control" required>
                <small class="text-muted">@lang('Use a negative number to deduct points, positive to grant.')</small>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('Reason')</label>
                <input type="text" name="reason" class="form-control" required>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Adjust Points')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
