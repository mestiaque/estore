@php
    $fieldName = $name ?? 'store_id';
    $current   = old($fieldName, $selected ?? '');
@endphp
<label class="form-label">@lang('Store')</label>
@if($stores->count() === 1)
    <input type="hidden" name="{{ $fieldName }}" value="{{ $stores->first()->id }}">
    <div class="form-control-plaintext fw-bold">{{ $stores->first()->name }}</div>
@else
    <select name="{{ $fieldName }}" class="form-control" required>
        <option value="">@lang('Select Store')</option>
        @foreach($stores as $store)
            <option value="{{ $store->id }}" {{ (string) $current === (string) $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
        @endforeach
    </select>
@endif
