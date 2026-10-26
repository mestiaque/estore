@extends('me::master')

@section('title', trans('Register Session'))

@push('buttons')
  @can('retail_cash_register_session.close')
    @if($session->isOpen())
    @component('me::components.btn.add-button', [
        'route' => route('retail.cash-register-sessions.close-form', $session->id),
        'text' => __('Close Register'),
        'class' => 'btn-encodex-deactive'
    ])
    @endcomponent
    @endif
  @endcan
@endpush

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Register')</th><td>{{ $session->cashRegister->name }}</td></tr>
            <tr><th>@lang('Opened By')</th><td>{{ $session->openedBy->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Opened At')</th><td>{{ $session->opened_at?->format('d M Y H:i') }}</td></tr>
            <tr><th>@lang('Opening Balance')</th><td>{{ number_format($session->opening_balance, 2) }}</td></tr>
            <tr><th>@lang('Current Balance (system)')</th><td class="fw-bold">{{ number_format($session->current_balance, 2) }}</td></tr>
            @if(!$session->isOpen())
                <tr><th>@lang('Closed By')</th><td>{{ $session->closedBy->name ?? __('N/A') }}</td></tr>
                <tr><th>@lang('Closed At')</th><td>{{ $session->closed_at?->format('d M Y H:i') }}</td></tr>
                <tr><th>@lang('Expected Closing Balance')</th><td>{{ number_format($session->expected_closing_balance, 2) }}</td></tr>
                <tr><th>@lang('Counted Closing Balance')</th><td>{{ number_format($session->closing_balance, 2) }}</td></tr>
                <tr>
                    <th>@lang('Difference')</th>
                    <td class="{{ $session->difference > 0 ? 'text-success' : ($session->difference < 0 ? 'text-danger' : '') }} fw-bold">
                        {{ number_format($session->difference, 2) }}
                    </td>
                </tr>
            @endif
            <tr><th>@lang('Notes')</th><td>{{ $session->notes ?? __('N/A') }}</td></tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Cash Movements')</b></div>
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
                        <td class="{{ $transaction->amount >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td>{{ number_format($transaction->balance_after, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">@lang('No cash movements found')</td>
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
