@extends('me::master')

@section('title', trans('Loyalty Report'))

@section('content')
@component('retail::reports._date-filter', ['from' => $from, 'to' => $to])
@endcomponent

<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Points Earned')</th><td class="text-end text-success">+{{ number_format($summary['earned'], 2) }}</td></tr>
            <tr><th>@lang('Points Redeemed')</th><td class="text-end text-danger">-{{ number_format($summary['redeemed'], 2) }}</td></tr>
            <tr><th>@lang('Points Reversed')</th><td class="text-end text-danger">-{{ number_format($summary['reversed'], 2) }}</td></tr>
            <tr><th>@lang('Total Point Liability (all customers, all time)')</th><td class="text-end fw-bold">{{ number_format($summary['liability'], 2) }}</td></tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Customer Levels')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>@lang('Level')</th>
                    <th class="text-end">@lang('Min Points')</th>
                    <th class="text-end">@lang('Customers')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($levels as $level)
                    <tr>
                        <td>{{ $level->name }}</td>
                        <td class="text-end">{{ $level->min_points }}</td>
                        <td class="text-end">{{ $level->customers_count }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">@lang('No customer levels found')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
