@php($plan = $plan ?? null)

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Bank')</label>
        <select name="emi_bank_id" class="form-control" required>
            <option value="">@lang('Select Bank')</option>
            @foreach($banks as $bank)
                <option value="{{ $bank->id }}" {{ old('emi_bank_id', $plan->emi_bank_id ?? '') == $bank->id ? 'selected' : '' }}>{{ $bank->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">@lang('Name')</label>
        <input type="text" name="name" class="form-control" value="{{ old('name', $plan->name ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Duration (months)')</label>
        <input type="number" min="1" max="60" name="duration_months" class="form-control" value="{{ old('duration_months', $plan->duration_months ?? '') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Interest Type')</label>
        <select name="interest_type" class="form-control" required>
            <option value="flat" {{ old('interest_type', $plan->interest_type->value ?? '') == 'flat' ? 'selected' : '' }}>@lang('Flat (one-time)')</option>
            <option value="monthly_flat" {{ old('interest_type', $plan->interest_type->value ?? '') == 'monthly_flat' ? 'selected' : '' }}>@lang('Monthly Flat')</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Interest Rate %')</label>
        <input type="number" step="0.01" min="0" name="interest_rate" class="form-control" value="{{ old('interest_rate', $plan->interest_rate ?? 0) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Fee Type')</label>
        <select name="fee_type" class="form-control" required>
            <option value="flat" {{ old('fee_type', $plan->fee_type->value ?? '') == 'flat' ? 'selected' : '' }}>@lang('Flat Amount')</option>
            <option value="percentage" {{ old('fee_type', $plan->fee_type->value ?? '') == 'percentage' ? 'selected' : '' }}>@lang('Percentage')</option>
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Fee Value')</label>
        <input type="number" step="0.01" min="0" name="fee_value" class="form-control" value="{{ old('fee_value', $plan->fee_value ?? 0) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Min Amount')</label>
        <input type="number" step="0.01" min="0" name="min_amount" class="form-control" value="{{ old('min_amount', $plan->min_amount ?? 0) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">@lang('Max Amount')</label>
        <input type="number" step="0.01" min="0" name="max_amount" class="form-control" value="{{ old('max_amount', $plan->max_amount ?? '') }}" placeholder="@lang('Unlimited')">
    </div>
    <div class="col-md-4 mb-3">
        <div class="form-check form-switch">
            <input type="checkbox" name="status" class="form-check-input" value="1" {{ old('status', $plan->status ?? true) ? 'checked' : '' }}>
            <label class="form-check-label">@lang('Active')</label>
        </div>
    </div>
</div>
