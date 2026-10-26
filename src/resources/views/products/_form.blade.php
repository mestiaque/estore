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
    {{-- A switched-off feature's field is kept as a hidden input so saving never wipes existing data. --}}
    @feature('barcode')
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Barcode')</label>
        <input type="text" name="barcode" class="form-control" value="{{ old('barcode', $product->barcode ?? request('barcode', '')) }}">
    </div>
    @else
        <input type="hidden" name="barcode" value="{{ $product->barcode ?? '' }}">
    @endfeature
    @feature('weighed_barcode')
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Scale PLU Code')</label>
        <input type="text" name="plu_code" class="form-control" value="{{ old('plu_code', $product->plu_code ?? '') }}" placeholder="@lang('For goods sold by weight from a label-printing scale')">
    </div>
    @else
        <input type="hidden" name="plu_code" value="{{ $product->plu_code ?? '' }}">
    @endfeature
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
    {{-- Batch/Serial are offered while their feature is on — or when this product already uses them. --}}
    @php($currentTrackType = old('track_type', $product->track_type ?? $productDefaults['track_type'] ?? 'normal'))
    @php($trackTypes = collect(['normal' => 'Normal', 'batch' => 'Batch (lot & expiry)', 'serial' => 'Serial / IMEI'])->filter(fn ($label, $value) => $value === 'normal' || $value === $currentTrackType || \ME\Retail\Support\RetailModules::enabled($value)))
    @if($trackTypes->count() > 1)
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Tracking Type')</label>
        <select name="track_type" class="form-control" required>
            @foreach($trackTypes as $value => $label)
                <option value="{{ $value }}" {{ $currentTrackType == $value ? 'selected' : '' }}>
                    @lang($label)
                </option>
            @endforeach
        </select>
    </div>
    @else
        <input type="hidden" name="track_type" value="normal">
    @endif
    @feature('loyalty')
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Loyalty Points Earned (per unit sold)')</label>
        <input type="number" step="0.01" min="0" name="earn_points" class="form-control" value="{{ old('earn_points', $product->earn_points ?? 0) }}">
    </div>
    @else
        <input type="hidden" name="earn_points" value="{{ $product->earn_points ?? 0 }}">
    @endfeature
    <div class="col-md-6 mb-3 d-flex align-items-end gap-4">
        @if(\ME\Retail\Support\RetailModules::enabled('batch') || ($product->has_expiry ?? false))
        <div class="form-check form-switch">
            <input type="checkbox" name="has_expiry" class="form-check-input" value="1" {{ old('has_expiry', $product->has_expiry ?? $productDefaults['has_expiry'] ?? false) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Has Expiry')</label>
        </div>
        @endif
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
