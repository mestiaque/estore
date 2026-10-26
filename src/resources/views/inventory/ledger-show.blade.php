@extends('me::master')

@section('title', trans('Stock Movement Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Product')</th><td>{{ $ledger->product->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Warehouse')</th><td>{{ $ledger->warehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Type')</th><td><span class="badge bg-info">{{ $ledger->movement_type->label() }}</span></td></tr>
            <tr><th>@lang('Quantity')</th><td>{{ $ledger->quantity }}</td></tr>
            <tr><th>@lang('Quantity Before')</th><td>{{ $ledger->quantity_before }}</td></tr>
            <tr><th>@lang('Quantity After')</th><td>{{ $ledger->quantity_after }}</td></tr>
            <tr><th>@lang('Reference')</th><td>{{ $ledger->reference_type ? class_basename($ledger->reference_type) . ' #' . $ledger->reference_id : __('N/A') }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $ledger->notes ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $ledger->created_at?->format('d M Y H:i') }}</td></tr>
        </table>
    </div>
</div>
@endsection
