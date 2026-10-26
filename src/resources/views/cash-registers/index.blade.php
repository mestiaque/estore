@extends('me::master')

@section('title', trans('Cash Registers'))

@push('buttons')
  @can('retail_cash_register.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.cash-registers.create'),
      'text' => __('Add Register'),
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
                    <th>@lang('Session')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($registers as $register)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $register->name }}</td>
                        <td>{{ $register->code }}</td>
                        <td>{{ $register->store->name ?? __('N/A') }}</td>
                        <td>
                            @if($register->openSession)
                                <span class="badge bg-success">@lang('Open')</span>
                            @else
                                <span class="badge bg-secondary">@lang('Closed')</span>
                            @endif
                        </td>
                        <td>
                            @if($register->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_cash_register.view')
                                <a href="{{ route('retail.cash-registers.show', $register->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_cash_register.edit')
                                <a href="{{ route('retail.cash-registers.edit', $register->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_cash_register_session.open')
                                    @if(!$register->openSession)
                                        <a href="{{ route('retail.cash-registers.open-form', $register->id) }}" class="btn btn-sm me-1 btn-encodex-active" title="@lang('Open')">
                                            <i class="fas fa-play"></i>
                                        </a>
                                    @endif
                                @endcan
                                @can('retail_cash_register.delete')
                                <form action="{{ route('retail.cash-registers.destroy', $register->id) }}" method="POST" class="d-inline">
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
                        <td colspan="7" class="text-center">@lang('No cash registers found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($registers->hasPages())
        <div class="mt-3">
            {{ $registers->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
