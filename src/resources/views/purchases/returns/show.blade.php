@extends('me::master')

@section('title', trans('Purchase Return Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Purchase Invoice')</th><td>{{ $purchaseReturn->purchase->invoice_number ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Supplier')</th><td>{{ $purchaseReturn->supplier->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Warehouse')</th><td>{{ $purchaseReturn->warehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $purchaseReturn->return_date?->format('d M Y') }}</td></tr>
            <tr><th>@lang('Total Refund')</th><td class="text-danger fw-bold">{{ number_format($purchaseReturn->total_refund, 2) }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $purchaseReturn->notes ?? __('N/A') }}</td></tr>
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
                    <th>@lang('Serial')</th>
                    <th>@lang('Quantity')</th>
                    <th>@lang('Refund Amount')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchaseReturn->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? __('N/A') }}</td>
                        <td>{{ $item->serial->serial_number ?? __('N/A') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->refund_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
