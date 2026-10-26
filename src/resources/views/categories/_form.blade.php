@php($category = $category ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $category->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Parent Category')</label>
        <select name="parent_id" class="form-control">
            <option value="">@lang('None')</option>
            @foreach($categories as $parent)
                <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id ?? '') == $parent->id ? 'selected' : '' }}>
                    {{ $parent->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Description')</label>
        <textarea name="description" class="form-control">{{ old('description', $category->description ?? '') }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $category->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
