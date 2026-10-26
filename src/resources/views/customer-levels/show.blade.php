@extends('me::master')

@section('title', trans('Customer Level Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $level->name }}</td></tr>
            <tr><th>@lang('Minimum Lifetime Points')</th><td>{{ $level->min_points }}</td></tr>
            <tr><th>@lang('Discount %')</th><td>{{ $level->discount_percent }}%</td></tr>
            <tr><th>@lang('Point Multiplier')</th><td>{{ $level->point_multiplier }}x</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($level->status)
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
