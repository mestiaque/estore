@extends('me::master')

@section('title', $type ? trans('Edit Business Type') : trans('Add Business Type'))

@section('content')
@include('retail::settings._nav')

<form action="{{ $type ? route('retail.settings.business-types.update', $type) : route('retail.settings.business-types.store') }}" method="POST">
    @csrf
    @if($type)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-lg-5 mb-3">
            <div class="card glass-card h-100" style="border-radius:.75rem">
                <div class="card-header"><b>@lang('Preset')</b></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">@lang('Name')</label>
                        <input type="text" name="label" class="form-control" value="{{ old('label', $preset['label']) }}" required maxlength="100">
                        @error('label')<small class="text-danger">{{ $message }}</small>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">@lang('Description')</label>
                        <textarea name="description" class="form-control" rows="3" maxlength="500">{{ old('description', $preset['description'] ?? '') }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">@lang('New products are tracked as')</label>
                        <select name="track_type" class="form-select" id="track-type">
                            @foreach(['normal' => 'Normal', 'batch' => 'Batch (lot & expiry)', 'serial' => 'Serial / IMEI'] as $value => $label)
                                <option value="{{ $value }}" {{ old('track_type', $preset['track_type']) === $value ? 'selected' : '' }}>@lang($label)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check form-switch mb-3" id="has-expiry-field">
                        <input type="hidden" name="has_expiry" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="has_expiry" name="has_expiry" value="1" {{ old('has_expiry', $preset['has_expiry'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="has_expiry">@lang('New batch products have an expiry date')</label>
                    </div>

                    <label class="form-label fw-semibold d-block">@lang('Units to create')</label>
                    @foreach($unitCatalog as $unitKey => $unit)
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" id="unit_{{ $unitKey }}" name="units[]" value="{{ $unitKey }}"
                                {{ in_array($unitKey, old('units', $preset['units'] ?? []), true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="unit_{{ $unitKey }}">{{ $unit['name'] }} ({{ $unit['short_name'] }})</label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-7 mb-3">
            <div class="card glass-card h-100" style="border-radius:.75rem">
                <div class="card-header"><b>@lang('Features this preset turns on')</b></div>
                <div class="card-body">
                    <p class="text-muted small">@lang('Untick a feature to switch it off when this preset is applied.')</p>
                    @php($disabled = old('disabled', $preset['disabled'] ?? []))
                    <div class="row">
                        @foreach($groups as $groupKey => $groupLabel)
                            <div class="col-md-6 mb-3">
                                <div class="fw-semibold small text-uppercase text-muted mb-1">@lang($groupLabel)</div>
                                @foreach($features as $featureKey => $feature)
                                    @continue($feature['group'] !== $groupKey)
                                    <div class="form-check form-switch">
                                        <input class="form-check-input preset-feature" type="checkbox" role="switch" id="feature_{{ $featureKey }}" value="{{ $featureKey }}"
                                            {{ in_array($featureKey, $disabled, true) ? '' : 'checked' }}>
                                        <label class="form-check-label" for="feature_{{ $featureKey }}">@lang($feature['label'])</label>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    {{-- The preset stores what it switches OFF; filled from the unticked switches on submit. --}}
                    <div id="disabled-inputs"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('retail.settings.business-type.edit') }}" class="btn btn-sm btn-outline-secondary mt-3">@lang('Cancel')</a>
        @component('me::components.btn.save-button', ['text' => $type ? __('Update Preset') : __('Add Preset')])
        @endcomponent
    </div>
</form>
@endsection

@push('js')
<script>
    (function () {
        const form = document.getElementById('disabled-inputs').closest('form');
        const trackType = document.getElementById('track-type');
        const expiryField = document.getElementById('has-expiry-field');

        function syncExpiry() {
            expiryField.style.display = trackType.value === 'batch' ? '' : 'none';
        }
        trackType.addEventListener('change', syncExpiry);
        syncExpiry();

        form.addEventListener('submit', function () {
            document.getElementById('disabled-inputs').innerHTML = Array.from(document.querySelectorAll('.preset-feature'))
                .filter(input => !input.checked)
                .map(input => `<input type="hidden" name="disabled[]" value="${input.value}">`)
                .join('');
        });
    })();
</script>
@endpush
