@extends('me::master')

@section('title', trans('Unit Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $unit->name }}</td></tr>
            <tr><th>@lang('Short Name')</th><td>{{ $unit->short_name }}</td></tr>
            <tr><th>@lang('Base Unit')</th><td>{{ $unit->baseUnit->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Conversion Factor')</th><td>{{ $unit->conversion_factor }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($unit->status)
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
