@extends('me::master')

@section('title', trans('Add Expense'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <form action="{{ route('retail.expenses.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    @include('retail::partials._store-select', ['stores' => $stores])
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Category')</label>
                    <select name="expense_category_id" class="form-control" required>
                        <option value="">@lang('Select Category')</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Amount')</label>
                    <input type="number" step="0.01" min="0.01" name="amount" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Expense Date')</label>
                    <input type="date" name="expense_date" class="form-control" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Payment Method')</label>
                    <select name="payment_method" id="payment-method-select" class="form-control" required>
                        <option value="cash">@lang('Cash')</option>
                        <option value="bank">@lang('Bank')</option>
                        <option value="card">@lang('Card')</option>
                        <option value="mobile_banking">@lang('Mobile Banking')</option>
                        <option value="other">@lang('Other')</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3" id="cash-session-field">
                    <label class="form-label">@lang('Cash Register Session')</label>
                    <select name="cash_register_session_id" class="form-control">
                        <option value="">@lang('None')</option>
                        @foreach($openSessions as $session)
                            <option value="{{ $session->id }}" {{ old('cash_register_session_id') == $session->id ? 'selected' : '' }}>
                                {{ $session->cashRegister->name }} (@lang('opened') {{ $session->opened_at?->format('d M H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Reference')</label>
                    <input type="text" name="reference" class="form-control">
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">@lang('Notes')</label>
                    <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                </div>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Save Expense')])
            @endcomponent
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
    const methodSelect = document.getElementById('payment-method-select');
    const cashSessionField = document.getElementById('cash-session-field');

    function toggleCashSessionField() {
        cashSessionField.style.display = methodSelect.value === 'cash' ? '' : 'none';
    }

    methodSelect.addEventListener('change', toggleCashSessionField);
    toggleCashSessionField();
</script>
@endpush
