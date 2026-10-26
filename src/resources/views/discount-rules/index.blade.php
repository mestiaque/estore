@extends('me::master')

@section('title', trans('Discount Rules'))

@push('buttons')
  @can('retail_discount_rule.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.discount-rules.create'),
      'text' => __('Add Discount Rule'),
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
                    <th>@lang('Type')</th>
                    <th>@lang('Value')</th>
                    <th>@lang('Usage')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rules as $rule)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            {{ $rule->name }}
                            @if($rule->coupon_code)
                                <br><span class="badge bg-info">{{ $rule->coupon_code }}</span>
                            @endif
                        </td>
                        <td>{{ $rule->type->label() }}</td>
                        <td>{{ $rule->discount_value }}{{ $rule->discount_type->value === 'percent' ? '%' : '' }}</td>
                        <td>{{ $rule->used_count }}{{ $rule->max_uses ? ' / ' . $rule->max_uses : '' }}</td>
                        <td>
                            @if($rule->isCurrentlyActive())
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_discount_rule.view')
                                <a href="{{ route('retail.discount-rules.show', $rule->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_discount_rule.edit')
                                <a href="{{ route('retail.discount-rules.edit', $rule->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_discount_rule.delete')
                                <form action="{{ route('retail.discount-rules.destroy', $rule->id) }}" method="POST" class="d-inline">
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
                        <td colspan="7" class="text-center">@lang('No discount rules found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($rules->hasPages())
        <div class="mt-3">
            {{ $rules->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
