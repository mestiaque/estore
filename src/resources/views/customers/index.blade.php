@extends('me::master')

@section('title', trans('Customers'))

@push('buttons')
  @can('retail_customer.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.customers.create'),
      'text' => __('Add Customer'),
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
                    <th>@lang('Phone')</th>
                    <th>@lang('Due Balance')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $customer->name }}</td>
                        <td>{{ $customer->phone ?? __('N/A') }}</td>
                        <td class="{{ $customer->due_balance > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($customer->due_balance, 2) }}
                        </td>
                        <td>
                            @if($customer->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_customer.view')
                                <a href="{{ route('retail.customers.show', $customer->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_customer.edit')
                                <a href="{{ route('retail.customers.edit', $customer->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_customer.delete')
                                <form action="{{ route('retail.customers.destroy', $customer->id) }}" method="POST" class="d-inline">
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
                        <td colspan="6" class="text-center">@lang('No customers found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($customers->hasPages())
        <div class="mt-3">
            {{ $customers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
