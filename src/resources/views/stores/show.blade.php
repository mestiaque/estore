@extends('me::master')

@section('title', trans('Store Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $store->name }}</td></tr>
            <tr><th>@lang('Code')</th><td>{{ $store->code }}</td></tr>
            <tr><th>@lang('Phone')</th><td>{{ $store->phone ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Email')</th><td>{{ $store->email ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Address')</th><td>{{ $store->address ?? __('N/A') }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($store->status)
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
