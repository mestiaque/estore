@extends('me::master')

@section('title', trans('Purchases'))

@push('buttons')
  @can('retail_purchase.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.purchases.create'),
      'text' => __('Add Purchase'),
      'class' => 'btn-encodex-create'
  ])
  @endcomponent
  @endcan
@endpush

@section('content')
<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>@lang('Invoice')</th>
                    <th>@lang('Supplier')</th>
                    <th>@lang('Store')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Grand Total')</th>
                    <th>@lang('Paid')</th>
                    <th>@lang('Due')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($purchases as $purchase)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $purchase->invoice_number }}</td>
                        <td>{{ $purchase->supplier->name ?? __('N/A') }}</td>
                        <td>{{ $purchase->store->name ?? __('N/A') }}</td>
                        <td>{{ $purchase->purchase_date?->format('d M Y') }}</td>
                        <td>{{ number_format($purchase->grand_total, 2) }}</td>
                        <td>{{ number_format($purchase->paid_amount, 2) }}</td>
                        <td>{{ number_format($purchase->due_amount, 2) }}</td>
                        <td>
                            @if($purchase->payment_status->value === 'paid')
                                <span class="badge bg-success">{{ $purchase->payment_status->label() }}</span>
                            @elseif($purchase->payment_status->value === 'partial')
                                <span class="badge bg-warning">{{ $purchase->payment_status->label() }}</span>
                            @else
                                <span class="badge bg-danger">{{ $purchase->payment_status->label() }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @can('retail_purchase.view')
                            <a href="{{ route('retail.purchases.show', $purchase->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">@lang('No purchases found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($purchases->hasPages())
        <div class="mt-3">
            {{ $purchases->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
