@php($unit = $unit ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $unit->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Short Name')</label>
        <input type="text" name="short_name" class="form-control" value="{{ old('short_name', $unit->short_name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Base Unit')</label>
        <select name="base_unit_id" class="form-control">
            <option value="">@lang('None (this is a base unit)')</option>
            @foreach($units as $base)
                <option value="{{ $base->id }}" {{ old('base_unit_id', $unit->base_unit_id ?? '') == $base->id ? 'selected' : '' }}>
                    {{ $base->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Conversion Factor')</label>
        <input type="number" step="0.0001" name="conversion_factor" class="form-control"
            value="{{ old('conversion_factor', $unit->conversion_factor ?? 1) }}" required>
        <small class="text-muted">@lang('How many base units make up 1 of this unit, e.g. 1 Carton = 24 Pieces.')</small>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $unit->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
