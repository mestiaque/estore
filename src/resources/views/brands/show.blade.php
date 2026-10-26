@extends('me::master')

@section('title', trans('Brand Details'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $brand->name }}</td></tr>
            <tr><th>@lang('Description')</th><td>{{ $brand->description ?? __('N/A') }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($brand->status)
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
