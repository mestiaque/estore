@extends('me::master')

@section('title', trans('Purchase Returns'))

@section('content')
<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>@lang('Purchase Invoice')</th>
                    <th>@lang('Supplier')</th>
                    <th>@lang('Refund Amount')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $return->purchase->invoice_number ?? __('N/A') }}</td>
                        <td>{{ $return->supplier->name ?? __('N/A') }}</td>
                        <td class="text-danger">{{ number_format($return->total_refund, 2) }}</td>
                        <td>{{ $return->return_date?->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('retail.purchases.returns.show', $return->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">@lang('No purchase returns found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($returns->hasPages())
        <div class="mt-3">
            {{ $returns->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
