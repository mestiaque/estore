@php
    $fieldName = $name ?? 'warehouse_id';
    $current   = old($fieldName, $selected ?? '');
@endphp
<label class="form-label">@lang('Warehouse')</label>
@if($warehouses->count() === 1)
    <input type="hidden" name="{{ $fieldName }}" value="{{ $warehouses->first()->id }}">
    <div class="form-control-plaintext fw-bold">{{ $warehouses->first()->name }}</div>
@else
    <select name="{{ $fieldName }}" class="form-control" required>
        <option value="">@lang('Select Warehouse')</option>
        @foreach($warehouses as $warehouse)
            <option value="{{ $warehouse->id }}" {{ (string) $current === (string) $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
        @endforeach
    </select>
@endif
