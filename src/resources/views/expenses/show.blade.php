@extends('me::master')

@section('title', trans('Expense Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Store')</th><td>{{ $expense->store->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Category')</th><td>{{ $expense->category->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Amount')</th><td class="text-danger fw-bold">{{ number_format($expense->amount, 2) }}</td></tr>
            <tr><th>@lang('Payment Method')</th><td>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td></tr>
            <tr><th>@lang('Cash Register Session')</th><td>{{ $expense->cashRegisterSession?->cashRegister?->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Date')</th><td>{{ $expense->expense_date?->format('d M Y') }}</td></tr>
            <tr><th>@lang('Reference')</th><td>{{ $expense->reference ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Notes')</th><td>{{ $expense->notes ?? __('N/A') }}</td></tr>
        </table>
    </div>
</div>
@endsection
