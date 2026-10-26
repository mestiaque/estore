@php($level = $level ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $level->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Minimum Lifetime Points')</label>
        <input type="number" step="0.01" min="0" name="min_points" class="form-control" value="{{ old('min_points', $level->min_points ?? 0) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Discount %')</label>
        <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="{{ old('discount_percent', $level->discount_percent ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Point Multiplier')</label>
        <input type="number" step="0.01" min="0" name="point_multiplier" class="form-control" value="{{ old('point_multiplier', $level->point_multiplier ?? 1) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Sort Order')</label>
        <input type="number" min="0" name="sl" class="form-control" value="{{ old('sl', $level->sl ?? 0) }}">
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $level->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
