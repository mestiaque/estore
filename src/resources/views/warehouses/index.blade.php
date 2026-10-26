@extends('me::master')

@section('title', trans('Warehouses'))

@push('buttons')
  @can('retail_warehouse.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.warehouses.create'),
      'text' => __('Add Warehouse'),
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
                    <th>@lang('Code')</th>
                    <th>@lang('Store')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warehouses as $warehouse)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $warehouse->name }}</td>
                        <td>{{ $warehouse->code }}</td>
                        <td>{{ $warehouse->store->name ?? __('N/A') }}</td>
                        <td>
                            @if($warehouse->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_warehouse.view')
                                <a href="{{ route('retail.warehouses.show', $warehouse->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_warehouse.edit')
                                <a href="{{ route('retail.warehouses.edit', $warehouse->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_warehouse.delete')
                                <form action="{{ route('retail.warehouses.destroy', $warehouse->id) }}" method="POST" class="d-inline">
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
                        <td colspan="6" class="text-center">@lang('No warehouses found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($warehouses->hasPages())
        <div class="mt-3">
            {{ $warehouses->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
