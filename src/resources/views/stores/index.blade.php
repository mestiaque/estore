@extends('me::master')

@section('title', trans('Stores'))

@push('buttons')
  @can('retail_store.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.stores.create'),
      'text' => __('Add Store'),
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
                    <th>@lang('Phone')</th>
                    <th>@lang('Email')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stores as $store)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $store->name }}</td>
                        <td>{{ $store->code }}</td>
                        <td>{{ $store->phone ?? __('N/A') }}</td>
                        <td>{{ $store->email ?? __('N/A') }}</td>
                        <td>
                            @if($store->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_store.view')
                                <a href="{{ route('retail.stores.show', $store->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_store.edit')
                                <a href="{{ route('retail.stores.edit', $store->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_store.delete')
                                <form action="{{ route('retail.stores.destroy', $store->id) }}" method="POST" class="d-inline">
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
                        <td colspan="7" class="text-center">@lang('No stores found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($stores->hasPages())
        <div class="mt-3">
            {{ $stores->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
