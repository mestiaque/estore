@php($supplier = $supplier ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $supplier->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Company')</label>
        <input type="text" name="company" class="form-control" value="{{ old('company', $supplier->company ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Phone')</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $supplier->phone ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Email')</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $supplier->email ?? '') }}">
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Address')</label>
        <textarea name="address" class="form-control">{{ old('address', $supplier->address ?? '') }}</textarea>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Tax Number')</label>
        <input type="text" name="tax_number" class="form-control" value="{{ old('tax_number', $supplier->tax_number ?? '') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Opening Balance')</label>
        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $supplier->opening_balance ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Credit Limit')</label>
        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $supplier->credit_limit ?? '') }}">
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Notes')</label>
        <textarea name="notes" class="form-control">{{ old('notes', $supplier->notes ?? '') }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $supplier->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
