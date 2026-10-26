@extends('me::master')

@section('title', trans('Cash Register Details'))

@push('buttons')
  @can('retail_cash_register_session.open')
    @if(!$register->openSession)
    @component('me::components.btn.add-button', [
        'route' => route('retail.cash-registers.open-form', $register->id),
        'text' => __('Open Register'),
        'class' => 'btn-encodex-active'
    ])
    @endcomponent
    @endif
  @endcan
@endpush

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $register->name }}</td></tr>
            <tr><th>@lang('Code')</th><td>{{ $register->code }}</td></tr>
            <tr><th>@lang('Store')</th><td>{{ $register->store->name ?? __('N/A') }}</td></tr>
            <tr>
                <th>@lang('Current Session')</th>
                <td>
                    @if($register->openSession)
                        <a href="{{ route('retail.cash-register-sessions.show', $register->openSession->id) }}">
                            <span class="badge bg-success">@lang('Open since'): {{ $register->openSession->opened_at?->format('d M Y H:i') }}</span>
                        </a>
                    @else
                        <span class="badge bg-secondary">@lang('No open session')</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Session History')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>@lang('Opened At')</th>
                    <th>@lang('Opening Balance')</th>
                    <th>@lang('Closed At')</th>
                    <th>@lang('Closing Balance')</th>
                    <th>@lang('Difference')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->opened_at?->format('d M Y H:i') }}</td>
                        <td>{{ number_format($session->opening_balance, 2) }}</td>
                        <td>{{ $session->closed_at?->format('d M Y H:i') ?? __('N/A') }}</td>
                        <td>{{ $session->closing_balance !== null ? number_format($session->closing_balance, 2) : __('N/A') }}</td>
                        <td class="{{ $session->difference > 0 ? 'text-success' : ($session->difference < 0 ? 'text-danger' : '') }}">
                            {{ $session->difference !== null ? number_format($session->difference, 2) : __('N/A') }}
                        </td>
                        <td>
                            @if($session->status->value === 'open')
                                <span class="badge bg-success">@lang('Open')</span>
                            @else
                                <span class="badge bg-secondary">@lang('Closed')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <a href="{{ route('retail.cash-register-sessions.show', $session->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">@lang('No sessions found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sessions->hasPages())
        <div class="mt-3 p-3">
            {{ $sessions->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
