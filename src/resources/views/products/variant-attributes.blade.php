@extends('me::master')

@section('title', trans('Variant Attributes'))

@section('content')
<div class="card glass-card w-100">
    <div class="card-body">
        <p>@lang('Product'): <strong>{{ $product->name }}</strong></p>
        <p class="text-muted">@lang('Select which attributes this product varies by (e.g. Color, Size). Next you will choose which values to generate.')</p>

        <form action="{{ route('retail.products.variants.attributes-store', $product->id) }}" method="POST">
            @csrf
            <div class="mb-3">
                @forelse($attributes as $attribute)
                    <div class="form-check">
                        <input type="checkbox" name="attribute_ids[]" value="{{ $attribute->id }}" class="form-check-input" id="attr-{{ $attribute->id }}"
                            {{ in_array($attribute->id, $selectedIds) ? 'checked' : '' }}>
                        <label class="form-check-label" for="attr-{{ $attribute->id }}">{{ $attribute->name }}</label>
                    </div>
                @empty
                    <p class="text-muted">@lang('No attributes defined yet.') <a href="{{ route('retail.attributes.create') }}">@lang('Create one')</a></p>
                @endforelse
            </div>
            @component('me::components.btn.save-button', ['text' => __('Save & Continue')])
            @endcomponent
        </form>
    </div>
</div>
@endsection
