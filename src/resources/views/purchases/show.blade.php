@extends('me::master')

@section('title', trans('Purchase Details'))

@push('buttons')
  @component('me::components.btn.add-button', [
      'route' => route('retail.purchases.invoice', $purchase->id),
      'text' => __('Print Invoice'),
      'class' => 'btn-encodex-print2',
      'attribute' => 'target="_blank"'
  ])
  @endcomponent
  @can('retail_purchase.return')
  @component('me::components.btn.add-button', [
      'route' => route('retail.purchases.returns.create', $purchase->id),
      'text' => __('Process Return'),
      'class' => 'btn-encodex-payment'
  ])
  @endcomponent
  @endcan
@endpush

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Invoice')</th><td>{{ $purchase->invoice_number }}</td></tr>
            <tr><th>@lang('Supplier')</th><td>{{ $purchase->supplier->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Store')</th><td>{{ $purchase->store->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Warehouse')</th><td>{{ $purchase->warehouse->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $purchase->purchase_date?->format('d M Y') }}</td></tr>
            <tr><th>@lang('Reference')</th><td>{{ $purchase->reference_number ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $purchase->notes ?? __('N/A') }}</td></tr>
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
                    <th>@lang('Price')</th>
                    <th>@lang('Discount')</th>
                    <th>@lang('Tax')</th>
                    <th>@lang('Total')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $item)
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
                                    @if($serial->warranty_end)
                                        <br><small class="text-muted">@lang('Warranty'): {{ $serial->warranty_months }} @lang('months') ({{ __('until') }} {{ $serial->warranty_end->format('d M Y') }})</small>
                                    @endif
                                @endforeach
                            @else
                                {{ __('N/A') }}
                            @endif
                        </td>
                        <td>{{ $item->unit->name ?? __('N/A') }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->purchase_price, 2) }}</td>
                        <td>{{ number_format($item->discount, 2) }}</td>
                        <td>{{ number_format($item->tax, 2) }}</td>
                        <td>{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="7" class="text-end">@lang('Subtotal')</th>
                    <td>{{ number_format($purchase->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="7" class="text-end">@lang('Discount')</th>
                    <td>{{ number_format($purchase->discount, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="7" class="text-end">@lang('Tax')</th>
                    <td>{{ number_format($purchase->tax, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="7" class="text-end">@lang('Grand Total')</th>
                    <td class="fw-bold">{{ number_format($purchase->grand_total, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="7" class="text-end">@lang('Paid')</th>
                    <td>{{ number_format($purchase->paid_amount, 2) }}</td>
                </tr>
                <tr>
                    <th colspan="7" class="text-end">@lang('Due')</th>
                    <td class="text-danger fw-bold">{{ number_format($purchase->due_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card glass-card w-100">
            <div class="card-header"><b>@lang('Payments')</b></div>
            <div class="table-responsive">
                <table class="table table-sm table-bordered table-encodex">
                    <thead class="text-center">
                        <tr>
                            <th>@lang('Date')</th>
                            <th>@lang('Amount')</th>
                            <th>@lang('Method')</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchase->payments as $payment)
                            <tr>
                                <td>{{ $payment->paid_at?->format('d M Y H:i') }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">@lang('No payments recorded')</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('retail_purchase.pay')
    @if($purchase->due_amount > 0)
    <div class="col-md-6">
        <div class="card glass-card w-100">
            <div class="card-header"><b>@lang('Record Payment')</b></div>
            <div class="card-body">
                <form action="{{ route('retail.purchases.payments.store', $purchase->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">@lang('Amount')</label>
                        <input type="number" step="0.01" max="{{ $purchase->due_amount }}" name="amount" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">@lang('Payment Method')</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">@lang('Cash')</option>
                            <option value="bank">@lang('Bank')</option>
                            <option value="card">@lang('Card')</option>
                            <option value="mobile_banking">@lang('Mobile Banking')</option>
                            <option value="other">@lang('Other')</option>
                        </select>
                    </div>
                    @if($openSessions->isNotEmpty())
                    <div class="mb-3">
                        <label class="form-label">@lang('Cash Register Session')</label>
                        <select name="cash_register_session_id" class="form-control">
                            <option value="">@lang('None')</option>
                            @foreach($openSessions as $session)
                                <option value="{{ $session->id }}">{{ $session->cashRegister->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="mb-3">
                        <label class="form-label">@lang('Reference')</label>
                        <input type="text" name="reference" class="form-control">
                    </div>
                    @component('me::components.btn.save-button', ['text' => __('Record Payment')])
                    @endcomponent
                </form>
            </div>
        </div>
    </div>
    @endif
    @endcan
</div>
@endsection
