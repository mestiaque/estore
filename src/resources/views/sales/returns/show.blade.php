@extends('me::master')

@section('title', trans('Sale Return Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Sale Invoice')</th><td>{{ $saleReturn->sale->invoice_number ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Customer')</th><td>{{ $saleReturn->customer->name ?? __('Walk-in Customer') }}</td></tr>
            <tr><th>@lang('Warehouse')</th><td>{{ $saleReturn->warehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $saleReturn->return_date?->format('d M Y') }}</td></tr>
            <tr><th>@lang('Total Refund')</th><td class="text-danger fw-bold">{{ number_format($saleReturn->total_refund, 2) }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $saleReturn->notes ?? __('N/A') }}</td></tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Returned Items')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Product')</th>
                    <th>@lang('Quantity')</th>
                    <th>@lang('Refund Amount')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleReturn->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? __('N/A') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->refund_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
