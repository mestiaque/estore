@php($store = $store ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $store->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Code')</label>
        <input type="text" name="code" class="form-control" value="{{ old('code', $store->code ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Phone')</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $store->phone ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Email')</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $store->email ?? '') }}">
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Address')</label>
        <textarea name="address" class="form-control">{{ old('address', $store->address ?? '') }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $store->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
