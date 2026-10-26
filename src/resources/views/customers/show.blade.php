@extends('me::master')

@section('title', trans('Customer Details'))

@push('buttons')
  @can('retail_loyalty.adjust')
  @component('me::components.btn.add-button', [
      'route' => route('retail.loyalty.adjust.create') . '?customer_id=' . $customer->id,
      'text' => __('Adjust Points'),
      'class' => 'btn-encodex-payment'
  ])
  @endcomponent
  @endcan
@endpush

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $customer->name }}</td></tr>
            <tr><th>@lang('Code')</th><td>{{ $customer->customer_code ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Phone')</th><td>{{ $customer->phone ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Email')</th><td>{{ $customer->email ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Due Balance')</th><td class="{{ $customer->due_balance > 0 ? 'text-danger' : 'text-success' }} fw-bold">{{ number_format($customer->due_balance, 2) }}</td></tr>
            <tr><th>@lang('Points Balance')</th><td class="fw-bold">{{ number_format($customer->points_balance, 2) }}</td></tr>
            <tr>
                <th>@lang('Customer Level')</th>
                <td>{{ $customer->level->name ?? __('N/A') }}</td>
            </tr>
            <tr>
                <th>@lang('Membership')</th>
                <td>
                    @if($customer->membership)
                        <span class="badge {{ $customer->hasActiveMembership() ? 'bg-success' : 'bg-danger' }}">{{ $customer->membership->name }}</span>
                        @if($customer->membership_expires_at)
                            <small class="text-muted">@lang('expires'): {{ $customer->membership_expires_at->format('d M Y') }}</small>
                        @endif
                    @else
                        {{ __('N/A') }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($customer->status)
                        <span class="badge bg-success">@lang('Active')</span>
                    @else
                        <span class="badge bg-danger">@lang('Inactive')</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100 mb-3">
    <div class="card-header"><b>@lang('Loyalty Points History')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Date')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Points')</th>
                    <th>@lang('Balance After')</th>
                    <th>@lang('Notes')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customer->loyaltyTransactions()->latest('id')->limit(20)->get() as $lt)
                    <tr>
                        <td>{{ $lt->created_at?->format('d M Y H:i') }}</td>
                        <td><span class="badge bg-info">{{ $lt->type->label() }}</span></td>
                        <td class="{{ $lt->points >= 0 ? 'text-success' : 'text-danger' }}">{{ $lt->points >= 0 ? '+' : '' }}{{ $lt->points }}</td>
                        <td>{{ $lt->balance_after }}</td>
                        <td>{{ $lt->notes ?? __('N/A') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">@lang('No points activity found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card glass-card w-100 mb-3">
    <div class="card-header"><b>@lang('Purchase History')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Invoice No')</th>
                    <th>@lang('Date')</th>
                    <th class="text-end">@lang('Grand Total')</th>
                    <th class="text-end">@lang('Paid')</th>
                    <th class="text-end">@lang('Due')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</td>
                        <td class="text-end">{{ number_format($sale->grand_total, 2) }}</td>
                        <td class="text-end">{{ number_format($sale->paid_amount, 2) }}</td>
                        <td class="text-end {{ (float) $sale->due_amount > 0 ? 'text-danger' : '' }}">{{ number_format($sale->due_amount, 2) }}</td>
                        <td class="text-center"><span class="badge bg-{{ $sale->payment_status->value === 'paid' ? 'success' : ($sale->payment_status->value === 'unpaid' ? 'danger' : 'warning') }}">{{ $sale->payment_status->label() }}</span></td>
                        <td class="text-center">
                            @can('retail_sale.view')
                            <a href="{{ route('retail.sales.show', $sale->id) }}" class="btn btn-sm btn-encodex-show">@lang('View')</a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">@lang('No purchase history found')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
        <div class="mt-3 p-3">
            {{ $sales->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Statement')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>@lang('Date')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Reference')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Balance After')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at?->format('d M Y H:i') }}</td>
                        <td><span class="badge bg-info">{{ $transaction->type->label() }}</span></td>
                        <td>{{ $transaction->reference_type ? class_basename($transaction->reference_type) . ' #' . $transaction->reference_id : __('N/A') }}</td>
                        <td class="{{ $transaction->amount >= 0 ? 'text-danger' : 'text-success' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td>{{ number_format($transaction->balance_after, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">@lang('No transactions found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="mt-3 p-3">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
