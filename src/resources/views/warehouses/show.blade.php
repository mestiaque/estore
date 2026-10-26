@extends('me::master')

@section('title', trans('Warehouse Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $warehouse->name }}</td></tr>
            <tr><th>@lang('Code')</th><td>{{ $warehouse->code }}</td></tr>
            <tr><th>@lang('Store')</th><td>{{ $warehouse->store->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Address')</th><td>{{ $warehouse->address ?? __('N/A') }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($warehouse->status)
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
