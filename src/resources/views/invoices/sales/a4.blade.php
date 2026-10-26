@extends('retail::printMaster2')

@section('title', $sale->invoice_number)

@section('contents')
@include('retail::invoices._header')

<table>
    <tr>
        <td style="width:50%">
            <strong>@lang('Bill To')</strong><br>
            {{ $sale->customer->name ?? __('Walk-in Customer') }}<br>
            @if($sale->customer)
                {{ $sale->customer->phone ?? '' }}<br>
                {{ $sale->customer->address ?? '' }}
            @endif
        </td>
        <td class="text-right">
            <strong>@lang('Invoice #'):</strong> {{ $sale->invoice_number }}<br>
            <strong>@lang('Date'):</strong> {{ $sale->sale_date?->format('d M Y') }}<br>
            <strong>@lang('Store'):</strong> {{ $sale->store->name ?? __('N/A') }}<br>
            <strong>@lang('Cashier'):</strong> {{ $sale->cashier->name ?? __('N/A') }}
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
        @foreach($sale->items as $item)
            <tr>
                <td>
                    {{ $item->product->name ?? __('N/A') }}
                    @if($item->batch)
                        <br><small>@lang('Batch'): {{ $item->batch->batch_number }}</small>
                    @elseif($item->serials->isNotEmpty())
                        <br><small>@lang('SN'): {{ $item->serials->pluck('serial_number')->implode(', ') }}</small>
                        @php $warrantyEnds = \ME\Retail\Support\RetailModules::enabled('warranty') ? $item->serials->pluck('warranty_end')->filter() : collect(); @endphp
                        @if($warrantyEnds->isNotEmpty())
                            <br><small>@lang('Warranty until'): {{ $warrantyEnds->map(fn($d) => $d->format('d M Y'))->implode(', ') }}</small>
                        @endif
                    @endif
                </td>
                <td>{{ $item->unit->name ?? __('N/A') }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">{{ number_format($item->sale_price, 2) }}</td>
                <td class="text-right">{{ number_format($item->discount + $item->auto_discount, 2) }}</td>
                <td class="text-right">{{ number_format($item->tax, 2) }}</td>
                <td class="text-right">{{ number_format($item->total, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<table>
    <tr class="subtotal-row"><td>@lang('Subtotal')</td><td class="text-right">{{ number_format($sale->subtotal, 2) }}</td></tr>
    <tr><td>@lang('Discount')</td><td class="text-right">{{ number_format($sale->discount, 2) }}</td></tr>
    @if($sale->auto_discount > 0)
        <tr>
            <td>@lang('Auto Discount') @if($sale->coupon_code) ({{ $sale->coupon_code }}) @endif</td>
            <td class="text-right">{{ number_format($sale->auto_discount, 2) }}</td>
        </tr>
    @endif
    <tr><td>@lang('Tax')</td><td class="text-right">{{ number_format($sale->tax, 2) }}</td></tr>
    @if($sale->points_redeemed > 0)
        <tr><td>@lang('Points Redeemed') ({{ $sale->points_redeemed }} pts)</td><td class="text-right">{{ number_format($sale->points_discount, 2) }}</td></tr>
    @endif
    <tr class="grandtotal-row"><td>@lang('Grand Total')</td><td class="text-right">{{ number_format($sale->grand_total, 2) }}</td></tr>
    <tr><td>@lang('Paid')</td><td class="text-right">{{ number_format($sale->paid_amount, 2) }}</td></tr>
    <tr><td>@lang('Due')</td><td class="text-right">{{ number_format($sale->due_amount, 2) }}</td></tr>
    @if($sale->points_earned > 0)
        <tr><td>@lang('Points Earned')</td><td class="text-right">+{{ $sale->points_earned }}</td></tr>
    @endif
</table>

@if($sale->payments->isNotEmpty())
<table>
    <thead>
        <tr>
            <th>@lang('Payment Method')</th>
            <th class="text-right">@lang('Amount')</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sale->payments as $payment)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif

@if($sale->notes)
    <p><strong>@lang('Notes'):</strong> {{ $sale->notes }}</p>
@endif

<div class="print-footer">
    <div class="signature-box">
        <div class="signature-line">@lang('Customer Signature')</div>
    </div>
    <div class="signature-box">
        <div class="signature-line">@lang('Authorized Signature')</div>
    </div>
</div>
<div class="print-time">@lang('Printed at'): {{ now()->format('d M Y H:i') }}</div>
@endsection
