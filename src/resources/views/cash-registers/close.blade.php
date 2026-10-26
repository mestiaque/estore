@extends('me::master')

@section('title', trans('Close Register'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <p>@lang('Register'): <strong>{{ $session->cashRegister->name }}</strong></p>
        <p>@lang('Expected Balance (system)'): <strong>{{ number_format($session->current_balance, 2) }}</strong></p>

        <form action="{{ route('retail.cash-register-sessions.close', $session->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">@lang('Counted Closing Balance')</label>
                <input type="number" step="0.01" min="0" name="closing_balance" class="form-control" required>
                <small class="text-muted">@lang('Enter the physically counted cash in the drawer.')</small>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('Notes')</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Close Register')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
