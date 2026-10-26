@extends('me::master')

@section('title', trans('Products'))

@push('buttons')
  @can('retail_barcode.print')
  @component('me::components.btn.add-button', [
      'route' => route('retail.barcode-labels.form'),
      'text' => __('Print Barcode Labels'),
      'class' => 'btn-encodex-print2'
  ])
  @endcomponent
  @endcan
  @can('retail_barcode.generate')
  <form action="{{ route('retail.products.barcode.bulk-generate') }}" method="POST" class="d-inline">
      @csrf
      <button type="submit" class="d-sm-inline-block btn btn-sm btn-encodex-list float-right ml-2">
          <i class="fas fa-barcode me-1"></i>@lang('Generate Missing Barcodes')
      </button>
  </form>
  @endcan
  @can('retail_product.create')
  @component('me::components.btn.add-button', [
      'route' => route('retail.products.create'),
      'text' => __('Add Product'),
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
                    <th>@lang('SKU')</th>
                    <th>@lang('Barcode')</th>
                    <th>@lang('Category')</th>
                    <th>@lang('Brand')</th>
                    <th>@lang('Unit')</th>
                    <th>@lang('Sale Price')</th>
                    <th>@lang('Stock')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $product->name }}</td>
                        <td>{{ $product->sku }}</td>
                        <td>
                            @if($product->barcode)
                                {{ $product->barcode }}
                            @else
                                <span class="text-muted">@lang('N/A')</span>
                                @can('retail_barcode.generate')
                                <form action="{{ route('retail.products.barcode.generate', $product->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-encodex-list" title="@lang('Generate Barcode')">
                                        <i class="fas fa-barcode"></i>
                                    </button>
                                </form>
                                @endcan
                            @endif
                        </td>
                        <td>{{ $product->category->name ?? __('N/A') }}</td>
                        <td>{{ $product->brand->name ?? __('N/A') }}</td>
                        <td>{{ $product->unit->short_name ?? __('N/A') }}</td>
                        <td>{{ number_format($product->sale_price, 2) }}</td>
                        <td>{{ $product->stock_quantity }}</td>
                        <td>
                            @if($product->status)
                                <span class="badge bg-success">@lang('Active')</span>
                            @else
                                <span class="badge bg-danger">@lang('Inactive')</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="d-inline-flex align-items-center gap-1">
                                @can('retail_product.view')
                                <a href="{{ route('retail.products.show', $product->id) }}" class="btn btn-sm me-1 btn-encodex-show" title="@lang('View')">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endcan
                                @can('retail_product.edit')
                                <a href="{{ route('retail.products.edit', $product->id) }}" class="btn btn-sm me-1 btn-encodex-edit" title="@lang('Edit')">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endcan
                                @can('retail_product.delete')
                                <form action="{{ route('retail.products.destroy', $product->id) }}" method="POST" class="d-inline">
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
                        <td colspan="11" class="text-center">@lang('No products found')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($products->hasPages())
        <div class="mt-3">
            {{ $products->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>
@endsection
