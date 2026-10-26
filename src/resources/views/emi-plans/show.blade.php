@extends('me::master')

@section('title', trans('EMI Plan Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Bank')</th><td>{{ $plan->bank->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Name')</th><td>{{ $plan->name }}</td></tr>
            <tr><th>@lang('Duration')</th><td>{{ $plan->duration_months }} @lang('months')</td></tr>
            <tr><th>@lang('Interest')</th><td>{{ $plan->interest_rate }}% ({{ $plan->interest_type->label() }})</td></tr>
            <tr><th>@lang('Fee')</th><td>{{ $plan->fee_value }}{{ $plan->fee_type->value === 'percentage' ? '%' : '' }} ({{ $plan->fee_type->label() }})</td></tr>
            <tr><th>@lang('Min Amount')</th><td>{{ $plan->min_amount }}</td></tr>
            <tr><th>@lang('Max Amount')</th><td>{{ $plan->max_amount ?? __('Unlimited') }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($plan->status)
                        <span class="badge bg-success">@lang('Active')</span>
                    @else
                        <span class="badge bg-danger">@lang('Inactive')</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
