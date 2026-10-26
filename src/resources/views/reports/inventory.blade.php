@extends('me::master')

@section('title', trans('Inventory Report'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-1 mb-3">
            @foreach(['low_stock' => 'Low Stock', 'out_of_stock' => 'Out of Stock', 'expiring' => 'Expiring Soon', 'expired' => 'Expired'] as $key => $label)
                <a href="{{ route('retail.reports.inventory', ['type' => $key]) }}"
                    class="btn btn-sm {{ $type === $key ? 'btn-encodex-active' : 'btn-encodex-list' }}">
                    @lang($label)
                </a>
            @endforeach
        </div>
        <div class="row">
            <div class="col-md-6">
                <strong>@lang('Stock Value (at cost)'):</strong> {{ number_format($valuation['cost_value'], 2) }}
            </div>
            <div class="col-md-6">
                <strong>@lang('Stock Value (at retail)'):</strong> {{ number_format($valuation['retail_value'], 2) }}
            </div>
        </div>
    </div>
</div>

<div class="card glass-card w-100">
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-hover table-striped table-encodex table-sm">
            @if(in_array($type, ['low_stock', 'out_of_stock']))
                <thead class="text-center">
                    <tr>
                        <th>@lang('Product')</th>
                        <th>@lang('Category')</th>
                        <th class="text-end">@lang('Stock')</th>
                        <th>@lang('Unit')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $product)
                        <tr>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->category->name ?? __('N/A') }}</td>
                            <td class="text-end">{{ $product->stock_quantity }}</td>
                            <td>{{ $product->unit->name ?? __('N/A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">@lang('No products found')</td></tr>
                    @endforelse
                </tbody>
            @else
                <thead class="text-center">
                    <tr>
                        <th>@lang('Product')</th>
                        <th>@lang('Warehouse')</th>
                        <th>@lang('Batch')</th>
                        <th>@lang('Expiry Date')</th>
                        <th class="text-end">@lang('Quantity')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $batch)
                        <tr>
                            <td>{{ $batch->product->name ?? __('N/A') }}</td>
                            <td>{{ $batch->warehouse->name ?? __('N/A') }}</td>
                            <td>{{ $batch->batch_number }}</td>
                            <td>{{ $batch->expiry_date?->format('d M Y') }}</td>
                            <td class="text-end">{{ $batch->quantity }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center">@lang('No batches found')</td></tr>
                    @endforelse
                </tbody>
            @endif
        </table>
    </div>
</div>
@endsection
