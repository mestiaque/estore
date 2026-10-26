@extends('me::master')

@section('title', trans('EMI Contracts'))

@section('content')
<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>@lang('Sale')</th>
                    <th>@lang('Customer')</th>
                    <th>@lang('Bank')</th>
                    <th>@lang('Plan')</th>
                    <th class="text-end">@lang('Financed')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contracts as $contract)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $contract->sale->invoice_number ?? __('N/A') }}</td>
                        <td>{{ $contract->customer->name ?? __('N/A') }}</td>
                        <td>{{ $contract->bank->name ?? __('N/A') }}</td>
                        <td>{{ $contract->plan->name ?? __('N/A') }}</td>
                        <td class="text-end">{{ number_format($contract->financed_amount, 2) }}</td>
                        <td>
                            @if($contract->status->value === 'active')
                                <span class="badge bg-warning">{{ $contract->status->label() }}</span>
                            @elseif($contract->status->value === 'completed')
                                <span class="badge bg-success">{{ $contract->status->label() }}</span>
                            @else
                                <span class="badge bg-secondary">{{ $contract->status->label() }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @can('retail_emi.view')
                            <a href="{{ route('retail.emi-contracts.show', $contract->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">@lang('No EMI contracts found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($contracts->hasPages())
        <div class="mt-3">
            {{ $contracts->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
