@extends('me::master')

@section('title', trans('Cash Register Report'))

@section('content')
@component('retail::reports._date-filter', ['from' => $from, 'to' => $to])
@endcomponent

<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>@lang('Register')</th>
                    <th>@lang('Opened At')</th>
                    <th class="text-end">@lang('Opening')</th>
                    <th class="text-end">@lang('Expected Closing')</th>
                    <th class="text-end">@lang('Counted Closing')</th>
                    <th class="text-end">@lang('Difference')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sessions as $session)
                    <tr>
                        <td>{{ $session->cashRegister->name ?? __('N/A') }}</td>
                        <td>{{ $session->opened_at?->format('d M Y H:i') }}</td>
                        <td class="text-end">{{ number_format($session->opening_balance, 2) }}</td>
                        <td class="text-end">{{ $session->expected_closing_balance !== null ? number_format($session->expected_closing_balance, 2) : __('N/A') }}</td>
                        <td class="text-end">{{ $session->closing_balance !== null ? number_format($session->closing_balance, 2) : __('N/A') }}</td>
                        <td class="text-end {{ $session->difference > 0 ? 'text-success' : ($session->difference < 0 ? 'text-danger' : '') }}">
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
                    <tr><td colspan="8" class="text-center">@lang('No sessions found')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
