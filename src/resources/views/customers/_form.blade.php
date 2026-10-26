@php($customer = $customer ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name ?? '') }}" required>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Customer Code')</label>
        <input type="text" name="customer_code" class="form-control" value="{{ old('customer_code', $customer->customer_code ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Phone')</label>
        <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Email')</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}">
    </div>
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Address')</label>
        <textarea name="address" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Opening Balance')</label>
        <input type="number" step="0.01" name="opening_balance" class="form-control" value="{{ old('opening_balance', $customer->opening_balance ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Credit Limit')</label>
        <input type="number" step="0.01" name="credit_limit" class="form-control" value="{{ old('credit_limit', $customer->credit_limit ?? '') }}">
    </div>
    @feature('membership')
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Membership')</label>
        <select name="membership_id" class="form-control">
            <option value="">@lang('None')</option>
            @foreach($memberships as $membership)
                <option value="{{ $membership->id }}" {{ old('membership_id', $customer->membership_id ?? '') == $membership->id ? 'selected' : '' }}>
                    {{ $membership->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Membership Expires At')</label>
        <input type="date" name="membership_expires_at" class="form-control" value="{{ old('membership_expires_at', optional($customer->membership_expires_at ?? null)->format('Y-m-d')) }}">
    </div>
    @else
        <input type="hidden" name="membership_id" value="{{ $customer->membership_id ?? '' }}">
        <input type="hidden" name="membership_expires_at" value="{{ optional($customer->membership_expires_at ?? null)->format('Y-m-d') }}">
    @endfeature
    <div class="col-md-12 mb-3">
        <label class="form-label">@lang('Notes')</label>
        <textarea name="notes" class="form-control">{{ old('notes', $customer->notes ?? '') }}</textarea>
    </div>
    <div class="col-md-6 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $customer->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
