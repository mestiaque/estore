@extends('retail::printMaster2')

@section('title', $purchase->invoice_number)

@section('contents')
@include('retail::invoices._header')

<table>
    <tr>
        <td style="width:50%">
            <strong>@lang('Supplier')</strong><br>
            {{ $purchase->supplier->name ?? __('N/A') }}<br>
            {{ $purchase->supplier->phone ?? '' }}<br>
            {{ $purchase->supplier->address ?? '' }}
        </td>
        <td class="text-right">
            <strong>@lang('Invoice #'):</strong> {{ $purchase->invoice_number }}<br>
            <strong>@lang('Date'):</strong> {{ $purchase->purchase_date?->format('d M Y') }}<br>
            <strong>@lang('Reference'):</strong> {{ $purchase->reference_number ?? __('N/A') }}<br>
            <strong>@lang('Store'):</strong> {{ $purchase->store->name ?? __('N/A') }}<br>
            <strong>@lang('Warehouse'):</strong> {{ $purchase->warehouse->name ?? __('N/A') }}
        </td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>@lang('Product')</th>
            <th>@lang('Unit')</th>
            <th class="text-right">@lang('Qty')</th>
            <th class="text-right">@lang('Price')</th>
            <th class="text-right">@lang('Discount')</th>
            <th class="text-right">@lang('Tax')</th>
            <th class="text-right">@lang('Total')</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->items as $item)
            <tr>
                <td>
                    {{ $item->product->name ?? __('N/A') }}
                    @if($item->batch)
                        <br><small>@lang('Batch'): {{ $item->batch->batch_number }}</small>
                    @elseif($item->serials->isNotEmpty())
                        <br><small>@lang('SN'): {{ $item->serials->pluck('serial_number')->implode(', ') }}</small>
                    @endif
                </td>
                <td>{{ $item->unit->name ?? __('N/A') }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->purchase_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table>
    <tr class="subtotal-row"><td>@lang('Subtotal')</td><td class="text-right">{{ number_format($purchase->subtotal, 2) }}</td></tr>
    <tr><td>@lang('Discount')</td><td class="text-right">{{ number_format($purchase->discount, 2) }}</td></tr>
    <tr><td>@lang('Tax')</td><td class="text-right">{{ number_format($purchase->tax, 2) }}</td></tr>
    <tr class="grandtotal-row"><td>@lang('Grand Total')</td><td class="text-right">{{ number_format($purchase->grand_total, 2) }}</td></tr>
    <tr><td>@lang('Paid')</td><td class="text-right">{{ number_format($purchase->paid_amount, 2) }}</td></tr>
    <tr><td>@lang('Due')</td><td class="text-right">{{ number_format($purchase->due_amount, 2) }}</td></tr>
</table>

@if($purchase->payments->isNotEmpty())
<table>
    <thead>
        <tr>
            <th>@lang('Payment Method')</th>
            <th class="text-right">@lang('Amount')</th>
        </tr>
    </thead>
    <tbody>
        @foreach($purchase->payments as $payment)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($purchase->notes)
    <p><strong>@lang('Notes'):</strong> {{ $purchase->notes }}</p>
@endif

<div class="print-footer">
    <div class="signature-box">
        <div class="signature-line">@lang('Received By')</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">@lang('Authorized Signature')</div>
    </div>
</div>
<div class="print-time">@lang('Printed at'): {{ now()->format('d M Y H:i') }}</div>
@endsection
