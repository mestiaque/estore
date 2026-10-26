@extends('me::master')

@section('title', trans('Supplier Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $supplier->name }}</td></tr>
            <tr><th>@lang('Company')</th><td>{{ $supplier->company ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Phone')</th><td>{{ $supplier->phone ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Email')</th><td>{{ $supplier->email ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Due Balance')</th><td class="{{ $supplier->due_balance > 0 ? 'text-danger' : 'text-success' }} fw-bold">{{ number_format($supplier->due_balance, 2) }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($supplier->status)
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
    <div class="card-header"><b>@lang('Statement')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>@lang('Date')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Reference')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Balance After')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at?->format('d M Y H:i') }}</td>
                        <td><span class="badge bg-info">{{ $transaction->type->label() }}</span></td>
                        <td>{{ $transaction->reference_type ? class_basename($transaction->reference_type) . ' #' . $transaction->reference_id : __('N/A') }}</td>
                        <td class="{{ $transaction->amount >= 0 ? 'text-danger' : 'text-success' }}">
                            {{ $transaction->amount >= 0 ? '+' : '' }}{{ number_format($transaction->amount, 2) }}
                        </td>
                        <td>{{ number_format($transaction->balance_after, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">@lang('No transactions found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transactions->hasPages())
        <div class="mt-3 p-3">
            {{ $transactions->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
