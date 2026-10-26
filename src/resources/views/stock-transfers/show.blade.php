@extends('me::master')

@section('title', trans('Stock Transfer Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Transfer #')</th><td>{{ $stockTransfer->transfer_number }}</td></tr>
            <tr><th>@lang('From Warehouse')</th><td>{{ $stockTransfer->fromWarehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('To Warehouse')</th><td>{{ $stockTransfer->toWarehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $stockTransfer->transfer_date?->format('d M Y') }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $stockTransfer->notes ?? __('N/A') }}</td></tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Items')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Product')</th>
                    <th>@lang('Unit')</th>
                    <th>@lang('Quantity')</th>
                    <th>@lang('Source Batch')</th>
                    <th>@lang('Destination Batch')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockTransfer->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? __('N/A') }}</td>
                        <td>{{ $item->unit->name ?? __('N/A') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->sourceBatch->batch_number ?? __('N/A') }}</td>
                        <td>{{ $item->destinationBatch->batch_number ?? __('N/A') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
