@extends('me::master')

@section('title', trans('Open Register'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <p>@lang('Register'): <strong>{{ $register->name }}</strong></p>
        <form action="{{ route('retail.cash-registers.open', $register->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label">@lang('Opening Balance')</label>
                <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="0" required>
            </div>
            <div class="mb-3">
                <label class="form-label">@lang('Notes')</label>
                <textarea name="notes" class="form-control"></textarea>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Open Register')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
