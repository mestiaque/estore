@extends('me::master')

@section('title', trans('Expenses'))

@push('buttons')
  @can('retail_expense.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.expenses.create'),
      'text' => __('Add Expense'),
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
                    <th>@lang('Store')</th>
                    <th>@lang('Category')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Method')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $expense->store->name ?? __('N/A') }}</td>
                        <td>{{ $expense->category->name ?? __('N/A') }}</td>
                        <td class="text-danger">{{ number_format($expense->amount, 2) }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $expense->payment_method)) }}</td>
                        <td>{{ $expense->expense_date?->format('d M Y') }}</td>
                        <td class="text-center">
                            @can('retail_expense.view')
                            <a href="{{ route('retail.expenses.show', $expense->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center">@lang('No expenses found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($expenses->hasPages())
        <div class="mt-3">
            {{ $expenses->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
