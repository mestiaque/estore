@extends('me::master')

@section('title', trans('Brands'))

@push('buttons')
  @can('retail_brand.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.brands.create'),
      'text' => __('Add Brand'),
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
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($brands as $brand)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $brand->name }}</td>
                        <td>
                            @if($brand->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_brand.view')
                                <a href="{{ route('retail.brands.show', $brand->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_brand.edit')
                                <a href="{{ route('retail.brands.edit', $brand->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_brand.delete')
                                <form action="{{ route('retail.brands.destroy', $brand->id) }}" method="POST" class="d-inline">
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
                        <td colspan="4" class="text-center">@lang('No brands found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($brands->hasPages())
        <div class="mt-3">
            {{ $brands->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
