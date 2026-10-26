@php($product = $product ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('SKU')</label>
        <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Barcode')</label>
        <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Category')</label>
        <select name="category_id" class="form-control" required>
            <option value="">@lang('Select Category')</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Brand')</label>
        <select name="brand_id" class="form-control">
            <option value="">@lang('None')</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id ?? '') == $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Unit')</label>
        <select name="unit_id" class="form-control" required>
            <option value="">@lang('Select Unit')</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                    {{ $unit->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Purchase Price')</label>
        <input type="number" step="0.01" name="purchase_price" class="form-control" value="{{ old('purchase_price', $product->purchase_price ?? 0) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Sale Price')</label>
        <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price ?? 0) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Wholesale Price')</label>
        <input type="number" step="0.01" name="wholesale_price" class="form-control" value="{{ old('wholesale_price', $product->wholesale_price ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Tracking Type')</label>
        <select name="track_type" class="form-control" required>
            @foreach(['normal' => 'Normal', 'batch' => 'Batch', 'serial' => 'Serial'] as $value => $label)
                <option value="{{ $value }}" {{ old('track_type', $product->track_type ?? 'normal') == $value ? 'selected' : '' }}>
                    @lang($label)
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Loyalty Points Earned (per unit sold)')</label>
        <input type="number" step="0.01" min="0" name="earn_points" class="form-control" value="{{ old('earn_points', $product->earn_points ?? 0) }}">
    </div>
    <div class="col-md-6 mb-3 d-flex align-items-end gap-4">
        <div class="form-check form-switch">
            <input type="checkbox" name="has_expiry" class="form-check-input" value="1" {{ old('has_expiry', $product->has_expiry ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Has Expiry')</label>
        </div>
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $product->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Notes')</label>
        <textarea name="notes" class="form-control">{{ old('notes', $product->notes ?? '') }}</textarea>
    </div>
</div>
