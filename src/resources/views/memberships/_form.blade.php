@php($membership = $membership ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $membership->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Code')</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $membership->code ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Fee')</label>
        <input type="number" step="0.01" min="0" name="fee" class="form-control" value="{{ old('fee', $membership->fee ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Duration (days)')</label>
        <input type="number" min="1" name="duration_days" class="form-control" value="{{ old('duration_days', $membership->duration_days ?? '') }}" placeholder="@lang('Leave blank for unlimited')">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Point Multiplier')</label>
        <input type="number" step="0.01" min="0" name="point_multiplier" class="form-control" value="{{ old('point_multiplier', $membership->point_multiplier ?? 1) }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Discount %')</label>
        <input type="number" step="0.01" min="0" max="100" name="discount_percent" class="form-control" value="{{ old('discount_percent', $membership->discount_percent ?? 0) }}">
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $membership->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
