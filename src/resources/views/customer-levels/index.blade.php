@extends('me::master')

@section('title', trans('Customer Levels'))

@push('buttons')
  @can('retail_customer_level.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.customer-levels.create'),
      'text' => __('Add Level'),
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
                    <th>@lang('Name')</th>
                    <th>@lang('Min Points')</th>
                    <th>@lang('Discount %')</th>
                    <th>@lang('Point Multiplier')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($levels as $level)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $level->name }}</td>
                        <td>{{ $level->min_points }}</td>
                        <td>{{ $level->discount_percent }}%</td>
                        <td>{{ $level->point_multiplier }}x</td>
                        <td>
                            @if($level->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_customer_level.view')
                                <a href="{{ route('retail.customer-levels.show', $level->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_customer_level.edit')
                                <a href="{{ route('retail.customer-levels.edit', $level->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_customer_level.delete')
                                <form action="{{ route('retail.customer-levels.destroy', $level->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-encodex-delete" title="@lang('Delete')"
                                        onclick="return confirm('{{ __('Are you sure you want to delete this?') }}')">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">@lang('No customer levels found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($levels->hasPages())
        <div class="mt-3">
            {{ $levels->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
