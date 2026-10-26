@extends('me::master')

@section('title', trans('EMI Contract Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Sale')</th><td><a href="{{ route('retail.sales.show', $contract->sale_id) }}">{{ $contract->sale->invoice_number ?? __('N/A') }}</a></td></tr>
            <tr><th>@lang('Customer')</th><td>{{ $contract->customer->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Bank')</th><td>{{ $contract->bank->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Plan')</th><td>{{ $contract->plan->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Principal')</th><td>{{ number_format($contract->principal_amount, 2) }}</td></tr>
            <tr><th>@lang('Interest')</th><td>{{ number_format($contract->interest_amount, 2) }}</td></tr>
            <tr><th>@lang('Fee')</th><td>{{ number_format($contract->fee_amount, 2) }}</td></tr>
            <tr><th>@lang('Total Payable')</th><td class="fw-bold">{{ number_format($contract->total_payable, 2) }}</td></tr>
            <tr><th>@lang('Down Payment')</th><td>{{ number_format($contract->down_payment, 2) }}</td></tr>
            <tr><th>@lang('Financed Amount')</th><td>{{ number_format($contract->financed_amount, 2) }}</td></tr>
            <tr><th>@lang('Start Date')</th><td>{{ $contract->start_date?->format('d M Y') }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($contract->status->value === 'active')
                        <span class="badge bg-warning">{{ $contract->status->label() }}</span>
                    @elseif($contract->status->value === 'completed')
                        <span class="badge bg-success">{{ $contract->status->label() }}</span>
                    @else
                        <span class="badge bg-secondary">{{ $contract->status->label() }}</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Installments')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>@lang('Due Date')</th>
                    <th class="text-end">@lang('Amount')</th>
                    <th class="text-end">@lang('Paid')</th>
                    <th class="text-end">@lang('Remaining')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Pay')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($contract->installments as $installment)
                    <tr>
                        <td>{{ $installment->installment_number }}</td>
                        <td>{{ $installment->due_date?->format('d M Y') }}</td>
                        <td class="text-end">{{ number_format($installment->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($installment->paid_amount, 2) }}</td>
                        <td class="text-end">{{ number_format($installment->remaining_amount, 2) }}</td>
                        <td>
                            @php
                                $badgeClass = match($installment->status->value) {
                                    'paid' => 'bg-success',
                                    'partial' => 'bg-info',
                                    'overdue' => 'bg-danger',
                                    'cancelled' => 'bg-secondary',
                                    default => 'bg-warning',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $installment->status->label() }}</span>
                        </td>
                        <td>
                            @can('retail_emi.pay')
                                @if($installment->remaining_amount > 0 && $installment->status->value !== 'cancelled')
                                <form action="{{ route('retail.emi-installments.pay', $installment->id) }}" method="POST" class="d-flex gap-1">
                                    @csrf
                                    <input type="number" step="0.01" min="0.01" max="{{ $installment->remaining_amount }}" name="amount" class="form-control form-control-sm" style="width:100px" value="{{ $installment->remaining_amount }}" required>
                                    <select name="payment_method" class="form-control form-control-sm" style="width:110px">
                                        <option value="cash">@lang('Cash')</option>
                                        <option value="bank">@lang('Bank')</option>
                                        <option value="card">@lang('Card')</option>
                                        <option value="mobile_banking">@lang('Mobile Banking')</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-encodex-active" title="@lang('Pay')">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
