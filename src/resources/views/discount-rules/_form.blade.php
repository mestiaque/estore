@php($rule = $rule ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $rule->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Type')</label>
        <select name="type" id="rule-type" class="form-control" required>
            @foreach(['product' => 'Product', 'category' => 'Category', 'brand' => 'Brand', 'quantity' => 'Quantity Break', 'coupon' => 'Coupon'] as $value => $label)
                <option value="{{ $value }}" {{ old('type', $rule->type->value ?? '') == $value ? 'selected' : '' }}>@lang($label)</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 mb-3" data-target-for="product">
        <label class="form-label">@lang('Product')</label>
        <select name="target_id" class="form-control target-select" data-for="product">
            <option value="">@lang('Select Product')</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" {{ old('target_id', $rule->target_id ?? '') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3" data-target-for="category">
        <label class="form-label">@lang('Category')</label>
        <select name="target_id" class="form-control target-select" data-for="category">
            <option value="">@lang('Select Category')</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}" {{ old('target_id', $rule->target_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3" data-target-for="brand">
        <label class="form-label">@lang('Brand')</label>
        <select name="target_id" class="form-control target-select" data-for="brand">
            <option value="">@lang('Select Brand')</option>
            @foreach($brands as $brand)
                <option value="{{ $brand->id }}" {{ old('target_id', $rule->target_id ?? '') == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3" data-target-for="quantity">
        <label class="form-label">@lang('Minimum Quantity')</label>
        <input type="number" step="0.0001" min="0" name="min_quantity" class="form-control" value="{{ old('min_quantity', $rule->min_quantity ?? '') }}">
    </div>
    <div class="col-md-6 mb-3" data-target-for="coupon">
        <label class="form-label">@lang('Coupon Code')</label>
        <input type="text" name="coupon_code" class="form-control" value="{{ old('coupon_code', $rule->coupon_code ?? '') }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Discount Type')</label>
        <select name="discount_type" class="form-control" required>
            <option value="percent" {{ old('discount_type', $rule->discount_type->value ?? '') == 'percent' ? 'selected' : '' }}>@lang('Percent')</option>
            <option value="flat" {{ old('discount_type', $rule->discount_type->value ?? '') == 'flat' ? 'selected' : '' }}>@lang('Flat Amount')</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Discount Value')</label>
        <input type="number" step="0.01" min="0" name="discount_value" class="form-control" value="{{ old('discount_value', $rule->discount_value ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Sort Order')</label>
        <input type="number" min="0" name="sl" class="form-control" value="{{ old('sl', $rule->sl ?? 0) }}">
    </div>

    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Starts At')</label>
        <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', optional($rule->starts_at ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Ends At')</label>
        <input type="date" name="ends_at" class="form-control" value="{{ old('ends_at', optional($rule->ends_at ?? null)->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Max Uses')</label>
        <input type="number" min="1" name="max_uses" class="form-control" value="{{ old('max_uses', $rule->max_uses ?? '') }}" placeholder="@lang('Unlimited')">
    </div>

    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $rule->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>

<script>
    (function () {
        const typeSelect = document.getElementById('rule-type');
        function refresh() {
            document.querySelectorAll('[data-target-for]').forEach(function (el) {
                el.style.display = el.dataset.targetFor === typeSelect.value ? '' : 'none';
            });
        }
        typeSelect.addEventListener('change', refresh);
        refresh();
    })();
</script>
