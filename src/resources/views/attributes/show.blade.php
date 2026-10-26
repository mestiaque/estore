@extends('me::master')

@section('title', trans('Attribute Details'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            <tr><th>@lang('Name')</th><td>{{ $attribute->name }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($attribute->status)
                        <span class="badge bg-success">@lang('Active')</span>
                    @else
                        <span class="badge bg-danger">@lang('Inactive')</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Values')</b></div>
    <div class="card-body">
        @can('retail_attribute.edit')
        <form action="{{ route('retail.attribute-values.store', $attribute->id) }}" method="POST" class="d-flex gap-2 mb-3">
            @csrf
            <input type="text" name="value" class="form-control form-control-sm" placeholder="@lang('e.g. Red, M, 8GB')" style="max-width:250px" required>
            <button type="submit" class="btn btn-sm btn-encodex-create">@lang('Add Value')</button>
        </form>
        @endcan

        <div class="d-flex flex-wrap gap-2">
            @forelse($attribute->values as $value)
                <span class="badge bg-info d-inline-flex align-items-center gap-2 p-2">
                    {{ $value->value }}
                    @can('retail_attribute.edit')
                    <form action="{{ route('retail.attribute-values.destroy', $value->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm p-0 text-white border-0 bg-transparent" title="@lang('Delete')"
                            onclick="return confirm('{{ __('Are you sure you want to delete this?') }}')">
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    @endcan
                </span>
            @empty
                <span class="text-muted">@lang('No values added yet')</span>
            @endforelse
        </div>
    </div>
</div>
@endsection
