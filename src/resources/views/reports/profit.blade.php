@extends('me::master')

@section('title', trans('Profit Report'))

@section('content')
@component('retail::reports._date-filter', ['from' => $from, 'to' => $to])
    @slot('extra')
        <div class="col-auto">
            <label class="form-label mb-0">@lang('Store')</label>
            <select name="store_id" class="form-control form-control-sm">
                <option value="">@lang('All Stores')</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}" {{ (string) $storeId === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                @endforeach
            </select>
        </div>
    @endslot
@endcomponent

<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Revenue')</th><td class="text-end">{{ number_format($data['revenue'], 2) }}</td></tr>
            <tr><th>@lang('Cost of Goods Sold')</th><td class="text-end">{{ number_format($data['cogs'], 2) }}</td></tr>
            <tr><th>@lang('Gross Profit')</th><td class="text-end fw-bold">{{ number_format($data['gross_profit'], 2) }}</td></tr>
            <tr><th>@lang('Sale Returns')</th><td class="text-end text-danger">-{{ number_format($data['returns'], 2) }}</td></tr>
            <tr><th>@lang('Expenses')</th><td class="text-end text-danger">-{{ number_format($data['expenses'], 2) }}</td></tr>
            <tr>
                <th>@lang('Net Profit')</th>
                <td class="text-end fw-bold {{ $data['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ number_format($data['net_profit'], 2) }}
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
