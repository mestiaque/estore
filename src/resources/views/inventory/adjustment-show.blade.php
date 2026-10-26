@extends('me::master')

@section('title', trans('Stock Adjustment Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Product')</th><td>{{ $adjustment->product->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Warehouse')</th><td>{{ $adjustment->warehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Reason')</th><td><span class="badge bg-secondary">{{ $adjustment->reason->label() }}</span></td></tr>
            <tr><th>@lang('Quantity Before')</th><td>{{ $adjustment->quantity_before }}</td></tr>
            <tr><th>@lang('Quantity Adjusted')</th><td>{{ $adjustment->quantity_adjusted }}</td></tr>
            <tr><th>@lang('Quantity After')</th><td>{{ $adjustment->quantity_after }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $adjustment->notes ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $adjustment->created_at?->format('d M Y H:i') }}</td></tr>
        </table>
    </div>
</div>
@endsection
