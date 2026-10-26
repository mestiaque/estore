@extends('me::master')

@section('title', trans('Stock Transfers'))

@push('buttons')
  @can('retail_inventory.transfer')
  @component('me::components.btn.add-button', [
      'route' => route('retail.stock-transfers.create'),
      'text' => __('New Transfer'),
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
                    <th>@lang('Transfer #')</th>
                    <th>@lang('From')</th>
                    <th>@lang('To')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $transfer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $transfer->transfer_number }}</td>
                        <td>{{ $transfer->fromWarehouse->name ?? __('N/A') }}</td>
                        <td>{{ $transfer->toWarehouse->name ?? __('N/A') }}</td>
                        <td>{{ $transfer->transfer_date?->format('d M Y') }}</td>
                        <td class="text-center">
                            @can('retail_inventory.view')
                            <a href="{{ route('retail.stock-transfers.show', $transfer->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">@lang('No stock transfers found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transfers->hasPages())
        <div class="mt-3">
            {{ $transfers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
