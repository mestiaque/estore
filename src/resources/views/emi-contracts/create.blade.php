@extends('me::master')

@section('title', trans('Setup EMI'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <p>@lang('Sale'): <strong>{{ $sale->invoice_number }}</strong> &mdash; {{ $sale->customer->name ?? __('N/A') }}</p>
        <p>@lang('Amount to Finance (sale due)'): <strong>{{ number_format($sale->due_amount, 2) }}</strong></p>

        <form action="{{ route('retail.emi-contracts.store', $sale->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Bank')</label>
                    <select name="emi_bank_id" id="bank-select" class="form-control" required>
                        <option value="">@lang('Select Bank')</option>
                        @foreach($banks as $bank)
                            <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Plan')</label>
                    <select name="emi_plan_id" id="plan-select" class="form-control" required>
                        <option value="">@lang('Select Bank First')</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Down Payment')</label>
                    <input type="number" step="0.01" min="0" max="{{ $sale->due_amount }}" name="down_payment" class="form-control" value="0">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">@lang('Start Date')</label>
                    <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
            </div>
            @component('me::components.btn.save-button', ['text' => __('Create EMI Contract')])
            @endcomponent
        </form>
    </div>
</div>
@endsection

@php
    // Computed into a plain variable first — Blade's @json() splits its argument on every
    // top-level comma, so an inline multi-key array literal must never be passed directly.
    $banksData = $banks->map(fn($b) => [
        'id' => $b->id,
        'plans' => $b->plans->map(fn($p) => ['id' => $p->id, 'name' => $p->name . ' (' . $p->duration_months . ' months, ' . $p->interest_rate . '%)']),
    ]);
@endphp
@push('js')
<script>
    const banks = @json($banksData);

    document.getElementById('bank-select').addEventListener('change', function () {
        const bank = banks.find(b => b.id == this.value);
        const planSelect = document.getElementById('plan-select');
        if (!bank) {
            planSelect.innerHTML = '<option value="">@lang('Select Bank First')</option>';
            return;
        }
        planSelect.innerHTML = '<option value="">@lang('Select Plan')</option>' +
            bank.plans.map(p => `<option value="${p.id}">${p.name}</option>`).join('');
    });
</script>
@endpush
