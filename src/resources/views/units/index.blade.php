@extends('me::master')

@section('title', trans('Units'))

@push('buttons')
  @can('retail_unit.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.units.create'),
      'text' => __('Add Unit'),
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
                    <th>@lang('Short Name')</th>
                    <th>@lang('Base Unit')</th>
                    <th>@lang('Conversion Factor')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $unit->name }}</td>
                        <td>{{ $unit->short_name }}</td>
                        <td>{{ $unit->baseUnit->name ?? __('N/A') }}</td>
                        <td>{{ $unit->conversion_factor }}</td>
                        <td>
                            @if($unit->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_unit.view')
                                <a href="{{ route('retail.units.show', $unit->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_unit.edit')
                                <a href="{{ route('retail.units.edit', $unit->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_unit.delete')
                                <form action="{{ route('retail.units.destroy', $unit->id) }}" method="POST" class="d-inline">
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
                        <td colspan="7" class="text-center">@lang('No units found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($units->hasPages())
        <div class="mt-3">
            {{ $units->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
