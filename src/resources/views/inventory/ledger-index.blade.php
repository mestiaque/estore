@extends('me::master')

@section('title', trans('Stock Ledger'))

@section('content')
<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>@lang('Product')</th>
                    <th>@lang('Warehouse')</th>
                    <th>@lang('Type')</th>
                    <th>@lang('Quantity')</th>
                    <th>@lang('Before')</th>
                    <th>@lang('After')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ledgers as $ledger)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $ledger->product->name ?? __('N/A') }}</td>
                        <td>{{ $ledger->warehouse->name ?? __('N/A') }}</td>
                        <td><span class="badge bg-info">{{ $ledger->movement_type->label() }}</span></td>
                        <td class="{{ $ledger->quantity >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $ledger->quantity >= 0 ? '+' : '' }}{{ $ledger->quantity }}
                        </td>
                        <td>{{ $ledger->quantity_before }}</td>
                        <td>{{ $ledger->quantity_after }}</td>
                        <td>{{ $ledger->created_at?->format('d M Y H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('retail.stock-ledgers.show', $ledger->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">@lang('No stock movements found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ledgers->hasPages())
        <div class="mt-3">
            {{ $ledgers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
