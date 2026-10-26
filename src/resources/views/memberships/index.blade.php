@extends('me::master')

@section('title', trans('Memberships'))

@push('buttons')
  @can('retail_membership.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.memberships.create'),
      'text' => __('Add Membership'),
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
                    <th>@lang('Fee')</th>
                    <th>@lang('Duration')</th>
                    <th>@lang('Discount %')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($memberships as $membership)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $membership->name }}</td>
                        <td>{{ $membership->code }}</td>
                        <td>{{ number_format($membership->fee, 2) }}</td>
                        <td>{{ $membership->duration_days ? $membership->duration_days . ' ' . __('days') : __('Unlimited') }}</td>
                        <td>{{ $membership->discount_percent }}%</td>
                        <td>
                            @if($membership->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_membership.view')
                                <a href="{{ route('retail.memberships.show', $membership->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_membership.edit')
                                <a href="{{ route('retail.memberships.edit', $membership->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_membership.delete')
                                <form action="{{ route('retail.memberships.destroy', $membership->id) }}" method="POST" class="d-inline">
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
                        <td colspan="8" class="text-center">@lang('No memberships found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($memberships->hasPages())
        <div class="mt-3">
            {{ $memberships->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
