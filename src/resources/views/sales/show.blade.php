@extends('me::master')

@section('title', trans('Sale Details'))

@push('buttons')
  {{-- Only the invoice formats switched on in Retail Settings are offered. --}}
  @foreach(['a4' => __('A4 Invoice'), 'thermal80' => __('80mm Receipt'), 'thermal58' => __('58mm Receipt')] as $invoiceType => $invoiceLabel)
  @continue(!in_array($invoiceType, \ME\Retail\Support\RetailSettings::get('invoice_types') ?: ['a4'], true))
  @component('me::components.btn.add-button', [
      'route' => route('retail.sales.invoice', ['sale' => $sale->id, 'type' => $invoiceType]),
      'text' => $invoiceLabel,
      'class' => 'btn-encodex-print2',
      'attribute' => 'target="_blank"'
  ])
  @endcomponent
  @endforeach
  @feature('sale_return')
  @can('retail_sale.return')
  @component('me::components.btn.add-button', [
      'route' => route('retail.sales.returns.create', $sale->id),
      'text' => __('Process Return'),
      'class' => 'btn-encodex-payment'
  ])
  @endcomponent
  @endcan
  @endfeature
  @feature('emi')
  @can('retail_emi.create')
    @if($sale->emiContract)
    @component('me::components.btn.add-button', [
        'route' => route('retail.emi-contracts.show', $sale->emiContract->id),
        'text' => __('View EMI'),
        'class' => 'btn-encodex-payment'
    ])
    @endcomponent
    @elseif($sale->due_amount > 0 && $sale->customer_id)
    @component('me::components.btn.add-button', [
        'route' => route('retail.emi-contracts.create', $sale->id),
        'text' => __('Setup EMI'),
        'class' => 'btn-encodex-payment'
    ])
    @endcomponent
    @endif
  @endcan
  @endfeature
@endpush

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Invoice')</th><td>{{ $sale->invoice_number }}</td></tr>
            <tr><th>@lang('Customer')</th><td>{{ $sale->customer->name ?? __('Walk-in Customer') }}</td></tr>
            <tr><th>@lang('Store')</th><td>{{ $sale->store->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Warehouse')</th><td>{{ $sale->warehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $sale->sale_date?->format('d M Y') }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $sale->notes ?? __('N/A') }}</td></tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100 mb-3">
    <div class="card-header"><b>@lang('Items')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Product')</th>
                    <th>@lang('Batch / Serials')</th>
                    <th>@lang('Unit')</th>
                    <th>@lang('Quantity')</th>
                    <th>@lang('Returned')</th>
                    <th>@lang('Price')</th>
                    <th>@lang('Discount')</th>
                    <th>@lang('Tax')</th>
                    <th>@lang('Total')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? __('N/A') }}</td>
                        <td>
                            @if($item->batch)
                                <span class="badge bg-info">{{ $item->batch->batch_number }}</span>
                                @if($item->batch->expiry_date)
                                    <br><small class="text-muted">@lang('Exp'): {{ $item->batch->expiry_date->format('d M Y') }}</small>
                                @endif
                            @elseif($item->serials->isNotEmpty())
                                @foreach($item->serials as $serial)
                                    <span class="badge bg-secondary">{{ $serial->serial_number }}</span>
                                    @if($serial->warranty_end && \ME\Retail\Support\RetailModules::enabled('warranty'))
                                        <br><small class="text-muted">@lang('Warranty until'): {{ $serial->warranty_end->format('d M Y') }}</small>
                                    @endif
                                @endforeach
                            @else
                                {{ __('N/A') }}
                            @endif
                        </td>
                        <td>{{ $item->unit->name ?? __('N/A') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->returned_quantity_base }}</td>
                        <td>{{ number_format($item->sale_price, 2) }}</td>
                        <td>
                            {{ number_format($item->discount, 2) }}
                            @if($item->auto_discount > 0)
                                <br><small class="text-success">+{{ number_format($item->auto_discount, 2) }} ({{ $item->discountRule->name ?? __('auto') }})</small>
                            @endif
                        </td>
                        <td>{{ number_format($item->tax, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="8" class="text-end">@lang('Subtotal')</th>
                    <td>{{ number_format($sale->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="8" class="text-end">@lang('Discount')</th>
                    <td>{{ number_format($sale->discount, 2) }}</td>
                </tr>
                @if($sale->auto_discount > 0)
                <tr>
                    <th colspan="8" class="text-end">
                        @lang('Auto Discount')
                        @if($sale->coupon_code)
                            <span class="badge bg-info">{{ $sale->coupon_code }}</span>
                        @endif
                    </th>
                    <td class="text-success">-{{ number_format($sale->auto_discount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <th colspan="8" class="text-end">@lang('Tax')</th>
                    <td>{{ number_format($sale->tax, 2) }}</td>
                </tr>
                @if($sale->points_redeemed > 0)
                <tr>
                    <th colspan="8" class="text-end">@lang('Points Redeemed') ({{ $sale->points_redeemed }} pts)</th>
                    <td class="text-danger">-{{ number_format($sale->points_discount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <th colspan="8" class="text-end">@lang('Grand Total')</th>
                    <td class="fw-bold">{{ number_format($sale->grand_total, 2) }}</td>
                </tr>
                @if($sale->points_earned > 0)
                <tr>
                    <th colspan="8" class="text-end">@lang('Points Earned')</th>
                    <td class="text-success">+{{ $sale->points_earned }}</td>
                </tr>
                @endif
                <tr>
                    <th colspan="8" class="text-end">@lang('Paid')</th>
                    <td>{{ number_format($sale->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="8" class="text-end">@lang('Due')</th>
                    <td class="text-danger fw-bold">{{ number_format($sale->due_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Payments')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Date')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Method')</th>
                    <th>@lang('Reference')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sale->payments as $payment)
                    <tr>
                        <td>{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                        <td>{{ $payment->reference ?? __('N/A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">@lang('No payments recorded')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
