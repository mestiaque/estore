@extends('me::master')

@section('title', trans('EMI Plans'))

@push('buttons')
  @can('retail_emi_plan.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.emi-plans.create'),
      'text' => __('Add Plan'),
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
                    <th>@lang('Bank')</th>
                    <th>@lang('Name')</th>
                    <th>@lang('Duration')</th>
                    <th>@lang('Interest')</th>
                    <th>@lang('Fee')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($plans as $plan)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $plan->bank->name ?? __('N/A') }}</td>
                        <td>{{ $plan->name }}</td>
                        <td>{{ $plan->duration_months }} @lang('months')</td>
                        <td>{{ $plan->interest_rate }}% ({{ $plan->interest_type->label() }})</td>
                        <td>{{ $plan->fee_value }}{{ $plan->fee_type->value === 'percentage' ? '%' : '' }}</td>
                        <td>
                            @if($plan->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_emi_plan.view')
                                <a href="{{ route('retail.emi-plans.show', $plan->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_emi_plan.edit')
                                <a href="{{ route('retail.emi-plans.edit', $plan->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_emi_plan.delete')
                                <form action="{{ route('retail.emi-plans.destroy', $plan->id) }}" method="POST" class="d-inline">
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
                        <td colspan="8" class="text-center">@lang('No EMI plans found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($plans->hasPages())
        <div class="mt-3">
            {{ $plans->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
