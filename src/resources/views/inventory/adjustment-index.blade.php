@extends('me::master')

@section('title', trans('Stock Adjustments'))

@push('buttons')
  @can('retail_inventory.adjust')
  @component('me::components.btn.add-button', [
      'route' => route('retail.stock-adjustments.create'),
      'text' => __('Adjust Stock'),
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
                    <th>@lang('Product')</th>
                    <th>@lang('Warehouse')</th>
                    <th>@lang('Reason')</th>
                    <th>@lang('Before')</th>
                    <th>@lang('Adjusted')</th>
                    <th>@lang('After')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adjustments as $adjustment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $adjustment->product->name ?? __('N/A') }}</td>
                        <td>{{ $adjustment->warehouse->name ?? __('N/A') }}</td>
                        <td><span class="badge bg-secondary">{{ $adjustment->reason->label() }}</span></td>
                        <td>{{ $adjustment->quantity_before }}</td>
                        <td class="{{ $adjustment->quantity_adjusted >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $adjustment->quantity_adjusted >= 0 ? '+' : '' }}{{ $adjustment->quantity_adjusted }}
                        </td>
                        <td>{{ $adjustment->quantity_after }}</td>
                        <td>{{ $adjustment->created_at?->format('d M Y H:i') }}</td>
                        <td class="text-center">
                            @can('retail_inventory.view')
                            <a href="{{ route('retail.stock-adjustments.show', $adjustment->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">@lang('No stock adjustments found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($adjustments->hasPages())
        <div class="mt-3">
            {{ $adjustments->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
