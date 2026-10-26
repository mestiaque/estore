@extends('me::master')

@section('title', trans('Discount Rule Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $rule->name }}</td></tr>
            <tr><th>@lang('Type')</th><td>{{ $rule->type->label() }}</td></tr>
            <tr><th>@lang('Coupon Code')</th><td>{{ $rule->coupon_code ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Minimum Quantity')</th><td>{{ $rule->min_quantity ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Discount')</th><td>{{ $rule->discount_value }}{{ $rule->discount_type->value === 'percent' ? '%' : '' }} ({{ $rule->discount_type->label() }})</td></tr>
            <tr><th>@lang('Starts At')</th><td>{{ $rule->starts_at?->format('d M Y') ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Ends At')</th><td>{{ $rule->ends_at?->format('d M Y') ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Usage')</th><td>{{ $rule->used_count }}{{ $rule->max_uses ? ' / ' . $rule->max_uses : '' }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($rule->isCurrentlyActive())
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
