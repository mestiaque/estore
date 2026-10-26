@extends('me::master')

@section('title', trans('Bank Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $bank->name }}</td></tr>
            <tr><th>@lang('Code')</th><td>{{ $bank->code }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($bank->status)
                        <span class="badge bg-success">@lang('Active')</span>
                    @else
                        <span class="badge bg-danger">@lang('Inactive')</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Plans')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Name')</th>
                    <th>@lang('Duration')</th>
                    <th>@lang('Interest')</th>
                    <th>@lang('Status')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bank->plans as $plan)
                    <tr>
                        <td>{{ $plan->name }}</td>
                        <td>{{ $plan->duration_months }} @lang('months')</td>
                        <td>{{ $plan->interest_rate }}% ({{ $plan->interest_type->label() }})</td>
                        <td>
                            @if($plan->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">@lang('No plans found')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
