@extends('me::master')

@section('title', trans('Product Details'))

@push('buttons')
  @feature('barcode')
  @can('retail_barcode.generate')
    @if(!$product->barcode)
    <form action="{{ route('retail.products.barcode.generate', $product->id) }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="d-sm-inline-block btn btn-sm btn-encodex-list float-right ml-2">
            <i class="fas fa-barcode me-1"></i>@lang('Generate Barcode')
        </button>
    </form>
    @endif
  @endcan
  @endfeature
  @feature('variants')
  @can('retail_product.edit')
    @if(!$product->isVariant())
    @component('me::components.btn.add-button', [
        'route' => route('retail.products.variants.attributes-form', $product->id),
        'text' => __('Manage Variants'),
        'class' => 'btn-encodex-edit'
    ])
    @endcomponent
    @endif
  @endcan
  @endfeature
@endpush

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <table class="table table-sm table-bordered table-encodex">
            @if($product->isVariant())
                <tr>
                    <th>@lang('Variant Of')</th>
                    <td><a href="{{ route('retail.products.show', $product->parent_product_id) }}">{{ $product->parent->name ?? __('N/A') }}</a></td>
                </tr>
                <tr><th>@lang('Attributes')</th><td>{{ $product->variantValues->pluck('value')->implode(' / ') }}</td></tr>
            @endif
            <tr><th>@lang('Name')</th><td>{{ $product->name }}</td></tr>
            <tr><th>@lang('SKU')</th><td>{{ $product->sku }}</td></tr>
            <tr><th>@lang('Barcode')</th><td>{{ $product->barcode ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Category')</th><td>{{ $product->category->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Brand')</th><td>{{ $product->brand->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Unit')</th><td>{{ $product->unit->name ?? __('N/A') }}</td></tr>
            <tr><th>@lang('Purchase Price')</th><td>{{ number_format($product->purchase_price, 2) }}</td></tr>
            <tr><th>@lang('Sale Price')</th><td>{{ number_format($product->sale_price, 2) }}</td></tr>
            <tr><th>@lang('Wholesale Price')</th><td>{{ $product->wholesale_price !== null ? number_format($product->wholesale_price, 2) : __('N/A') }}</td></tr>
            <tr><th>@lang('Stock Quantity')</th><td>{{ $product->stock_quantity }}</td></tr>
            <tr><th>@lang('Tracking Type')</th><td>{{ ucfirst($product->track_type) }}</td></tr>
            <tr><th>@lang('Has Expiry')</th><td>{{ $product->has_expiry ? __('Yes') : __('No') }}</td></tr>
            <tr><th>@lang('Loyalty Points (per unit)')</th><td>{{ $product->earn_points }}</td></tr>
            <tr>
                <th>@lang('Status')</th>
                <td>
                    @if($product->status)
                        <span class="badge bg-success">@lang('Active')</span>
                    @else
                        <span class="badge bg-danger">@lang('Inactive')</span>
                    @endif
                </td>
            </tr>
            <tr><th>@lang('Notes')</th><td>{{ $product->notes ?? __('N/A') }}</td></tr>
        </table>
    </div>
</div>

@if(!$product->isVariant() && $product->variants->isNotEmpty())
<div class="card glass-card w-100 mt-3">
    <div class="card-header"><b>@lang('Variants')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Name')</th>
                    <th>@lang('SKU')</th>
                    <th>@lang('Attributes')</th>
                    <th>@lang('Sale Price')</th>
                    <th>@lang('Stock')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->variants as $variant)
                    <tr>
                        <td>{{ $variant->name }}</td>
                        <td>{{ $variant->sku }}</td>
                        <td>{{ $variant->variantValues->pluck('value')->implode(' / ') }}</td>
                        <td>{{ number_format($variant->sale_price, 2) }}</td>
                        <td>{{ $variant->stock_quantity }}</td>
                        <td class="text-center">
                            <a href="{{ route('retail.products.show', $variant->id) }}" class="btn btn-sm btn-encodex-show" title="@lang('View')">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
