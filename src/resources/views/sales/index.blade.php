@extends('me::master')

@section('title', trans('Sales'))

@push('buttons')
  @can('retail_sale.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.sales.create'),
      'text' => __('New Sale'),
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
                    <th>@lang('Customer')</th>
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
                @forelse($sales as $sale)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $sale->invoice_number }}</td>
                        <td>{{ $sale->customer->name ?? __('Walk-in Customer') }}</td>
                        <td>{{ $sale->store->name ?? __('N/A') }}</td>
                        <td>{{ $sale->sale_date?->format('d M Y') }}</td>
                        <td>{{ number_format($sale->grand_total, 2) }}</td>
                        <td>{{ number_format($sale->paid_amount, 2) }}</td>
                        <td>{{ number_format($sale->due_amount, 2) }}</td>
                        <td>
                            @if($sale->payment_status->value === 'paid')
                                <span class="badge bg-success">{{ $sale->payment_status->label() }}</span>
                            @elseif($sale->payment_status->value === 'partial')
                                <span class="badge bg-warning">{{ $sale->payment_status->label() }}</span>
                            @else
                                <span class="badge bg-danger">{{ $sale->payment_status->label() }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @can('retail_sale.view')
                            <a href="{{ route('retail.sales.show', $sale->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center">@lang('No sales found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($sales->hasPages())
        <div class="mt-3">
            {{ $sales->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
