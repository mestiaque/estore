@extends('me::master')

@section('title', trans('EMI Report'))

@section('content')
<div class="row mb-3">
    <div class="col-md-2 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Active Contracts')</h6>
            <h4>{{ $summary['active_contracts'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Completed Contracts')</h6>
            <h4>{{ $summary['completed_contracts'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Paid Installments')</h6>
            <h4 class="text-success">{{ $summary['paid_installments'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Pending Installments')</h6>
            <h4 class="text-warning">{{ $summary['pending_installments'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Overdue Installments')</h6>
            <h4 class="text-danger">{{ $summary['overdue_installments'] }}</h4>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="card glass-card p-3">
            <h6 class="text-muted">@lang('Overdue Amount')</h6>
            <h4 class="text-danger">{{ number_format($summary['overdue_amount'], 2) }}</h4>
        </div>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Overdue Installments')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>@lang('Sale')</th>
                    <th>@lang('Customer')</th>
                    <th>@lang('Installment #')</th>
                    <th>@lang('Due Date')</th>
                    <th class="text-end">@lang('Remaining')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($overdue as $installment)
                    <tr>
                        <td>{{ $installment->contract->sale->invoice_number ?? __('N/A') }}</td>
                        <td>{{ $installment->contract->customer->name ?? __('N/A') }}</td>
                        <td>{{ $installment->installment_number }}</td>
                        <td>{{ $installment->due_date?->format('d M Y') }}</td>
                        <td class="text-end">{{ number_format($installment->remaining_amount, 2) }}</td>
                        <td class="text-center">
                            <a href="{{ route('retail.emi-contracts.show', $installment->contract->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center">@lang('No overdue installments')</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
