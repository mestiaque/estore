@extends('me::master')

@section('title', trans('Membership Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $membership->name }}</td></tr>
            <tr><th>@lang('Code')</th><td>{{ $membership->code }}</td></tr>
            <tr><th>@lang('Fee')</th><td>{{ number_format($membership->fee, 2) }}</td></tr>
            <tr><th>@lang('Duration')</th><td>{{ $membership->duration_days ? $membership->duration_days . ' ' . __('days') : __('Unlimited') }}</td></tr>
            <tr><th>@lang('Discount %')</th><td>{{ $membership->discount_percent }}%</td></tr>
            <tr><th>@lang('Point Multiplier')</th><td>{{ $membership->point_multiplier }}x</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($membership->status)
                        <span class="badge bg-success">@lang('Active')</span>
                    @else
                        <span class="badge bg-danger">@lang('Inactive')</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>
@endsection
