@php($brand = $brand ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $brand->name ?? '') }}" required>
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Description')</label>
        <textarea name="description" class="form-control">{{ old('description', $brand->description ?? '') }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $brand->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
