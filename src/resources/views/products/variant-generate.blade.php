@extends('me::master')

@section('title', trans('Generate Variants'))

@section('content')
<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <p>@lang('Product'): <strong>{{ $product->name }}</strong></p>

        @if($product->variantAttributes->isEmpty())
            <p class="text-muted">
                @lang('No variant attributes selected yet.')
                <a href="{{ route('retail.products.variants.attributes-form', $product->id) }}">@lang('Select attributes first')</a>
            </p>
        @else
            <form action="{{ route('retail.products.variants.generate', $product->id) }}" method="POST">
                @csrf
                @foreach($product->variantAttributes as $attribute)
                    <div class="mb-3">
                        <label class="form-label fw-bold">{{ $attribute->name }}</label>
                        <div class="d-flex flex-wrap gap-3">
                            @forelse($attribute->values as $value)
                                <div class="form-check">
                                    <input type="checkbox" name="values[{{ $attribute->id }}][]" value="{{ $value->id }}" class="form-check-input" id="val-{{ $value->id }}">
                                    <label class="form-check-label" for="val-{{ $value->id }}">{{ $value->value }}</label>
                                </div>
                            @empty
                                <span class="text-muted">@lang('No values for this attribute yet.') <a href="{{ route('retail.attributes.show', $attribute->id) }}">@lang('Add some')</a></span>
                            @endforelse
                        </div>
                    </div>
                @endforeach
                @component('me::components.btn.save-button', ['text' => __('Generate Variants')])
                @endcomponent
            </form>
        @endif
    </div>
</div>

@if($product->variants->isNotEmpty())
<div class="card glass-card w-100">
    <div class="card-header"><b>@lang('Existing Variants')</b></div>
    <div class="table-responsive">
        <table class="table table-sm table-bordered table-encodex">
            <thead class="text-center">
                <tr>
                    <th>@lang('Name')</th>
                    <th>@lang('SKU')</th>
                    <th>@lang('Attributes')</th>
                    <th>@lang('Actions')</th>
                </tr>
            </thead>
            <tbody>
                @foreach($product->variants as $variant)
                    <tr>
                        <td>{{ $variant->name }}</td>
                        <td>{{ $variant->sku }}</td>
                        <td>{{ $variant->variantValues->pluck('value')->implode(' / ') }}</td>
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
