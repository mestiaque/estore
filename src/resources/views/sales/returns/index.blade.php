@extends('me::master')

@section('title', trans('Sale Returns'))

@section('content')
<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            <thead class="text-center">
                <tr>
                    <th>#</th>
                    <th>@lang('Sale Invoice')</th>
                    <th>@lang('Customer')</th>
                    <th>@lang('Refund Amount')</th>
                    <th>@lang('Date')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($returns as $return)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $return->sale->invoice_number ?? __('N/A') }}</td>
                        <td>{{ $return->customer->name ?? __('Walk-in Customer') }}</td>
                        <td class="text-danger">{{ number_format($return->total_refund, 2) }}</td>
                        <td>{{ $return->return_date?->format('d M Y') }}</td>
                        <td class="text-center">
                            <a href="{{ route('retail.sales.returns.show', $return->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">@lang('No sale returns found')</td>
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
