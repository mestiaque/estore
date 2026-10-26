<div class="thermal-line"><span>@lang('Invoice #')</span><span>{{ $sale->invoice_number }}</span></div>
<div class="thermal-line"><span>@lang('Date')</span><span>{{ $sale->sale_date?->format('d M Y') }}</span></div>
<div class="thermal-line"><span>@lang('Customer')</span><span>{{ $sale->customer->name ?? __('Walk-in') }}</span></div>
<div class="thermal-line"><span>@lang('Cashier')</span><span>{{ $sale->cashier->name ?? __('N/A') }}</span></div>

<div class="thermal-divider"></div>

@foreach($sale->items as $item)
    @php $warrantyEnds = $item->serials->pluck('warranty_end')->filter(); @endphp
    <div class="thermal-item">
        <div>{{ $item->product->name ?? __('N/A') }}</div>
        <div class="thermal-line">
            <span>{{ $item->quantity }} {{ $item->unit->name ?? '' }} x {{ number_format($item->sale_price, 2) }}</span>
            <span>{{ number_format($item->total, 2) }}</span>
        </div>
        @if($warrantyEnds->isNotEmpty())
            <div>@lang('Warranty until'): {{ $warrantyEnds->map(fn($d) => $d->format('d M Y'))->implode(', ') }}</div>
        @endif
    </div>
@endforeach

<div class="thermal-divider"></div>

<div class="thermal-line"><span>@lang('Subtotal')</span><span>{{ number_format($sale->subtotal, 2) }}</span></div>
<div class="thermal-line"><span>@lang('Discount')</span><span>{{ number_format($sale->discount + $sale->auto_discount, 2) }}</span></div>
<div class="thermal-line"><span>@lang('Tax')</span><span>{{ number_format($sale->tax, 2) }}</span></div>
@if($sale->points_redeemed > 0)
    <div class="thermal-line"><span>@lang('Points Used')</span><span>-{{ number_format($sale->points_discount, 2) }}</span></div>
@endif
<div class="thermal-line" style="font-weight:bold;"><span>@lang('Total')</span><span>{{ number_format($sale->grand_total, 2) }}</span></div>
<div class="thermal-line"><span>@lang('Paid')</span><span>{{ number_format($sale->paid_amount, 2) }}</span></div>
<div class="thermal-line"><span>@lang('Due')</span><span>{{ number_format($sale->due_amount, 2) }}</span></div>
@if($sale->points_earned > 0)
    <div class="thermal-line"><span>@lang('Points Earned')</span><span>+{{ $sale->points_earned }}</span></div>
@endif

<div class="thermal-divider"></div>

@foreach($sale->payments as $payment)
    <div class="thermal-line"><span>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span><span>{{ number_format($payment->amount, 2) }}</span></div>
@endforeach
