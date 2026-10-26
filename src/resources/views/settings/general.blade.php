@extends('me::master')

@section('title', trans('Retail Settings'))

@section('content')
@include('retail::settings._nav')

<form action="{{ route('retail.settings.general.update') }}" method="POST" id="general-settings-form">
    @csrf
    @method('PUT')

    <p class="text-muted small mb-3">
        @lang('Values used across the system. Settings of a switched-off feature are hidden here and keep their last value.')
    </p>

    <div class="row">
        @foreach($groups as $groupKey => $group)
            @continue(!isset($settings[$groupKey]))
            <div class="col-xl-6 mb-3">
                <div class="card glass-card h-100" style="border-radius:.75rem">
                    <div class="card-header"><b><i class="{{ $group['icon'] }}"></i> @lang($group['label'])</b></div>
                    <div class="card-body">
                        @foreach($settings[$groupKey] as $setting)
                            @php($name = 'settings[' . $setting['field'] . ']')
                            @php($id = 'setting_' . $setting['field'])
                            @php($error = 'settings.' . $setting['field'])
                            <div class="mb-3">
                                @switch($setting['type'])
                                    @case('bool')
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="{{ $name }}" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="{{ $id }}" name="{{ $name }}" value="1" {{ old($error, $setting['value']) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-semibold" for="{{ $id }}">@lang($setting['label'])</label>
                                        </div>
                                        @break

                                    @case('select')
                                        <label class="form-label fw-semibold" for="{{ $id }}">@lang($setting['label'])</label>
                                        <select class="form-select" id="{{ $id }}" name="{{ $name }}">
                                            @foreach($setting['options'] as $value => $label)
                                                <option value="{{ $value }}" {{ (string) old($error, $setting['value']) === (string) $value ? 'selected' : '' }}>@lang($label)</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('multiselect')
                                        <label class="form-label fw-semibold d-block">@lang($setting['label'])</label>
                                        @foreach($setting['options'] as $value => $label)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="checkbox" id="{{ $id }}_{{ $value }}" name="{{ $name }}[]" value="{{ $value }}"
                                                    {{ in_array($value, (array) old($error, $setting['value']), true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $id }}_{{ $value }}">@lang($label)</label>
                                            </div>
                                        @endforeach
                                        @break

                                    @case('csv')
                                        <label class="form-label fw-semibold" for="{{ $id }}">@lang($setting['label'])</label>
                                        <input type="text" class="form-control" id="{{ $id }}" name="{{ $name }}" value="{{ old($error, implode(',', (array) $setting['value'])) }}">
                                        @break

                                    @case('sizes')
                                        <label class="form-label fw-semibold">@lang($setting['label'])</label>
                                        <div class="label-sizes" data-name="{{ $name }}">
                                            @foreach(array_values((array) old($error, $setting['value'])) as $i => $size)
                                                <div class="input-group input-group-sm mb-1 label-size-row">
                                                    <span class="input-group-text">@lang('W')</span>
                                                    <input type="number" step="0.1" min="10" class="form-control" name="{{ $name }}[{{ $i }}][width]" value="{{ $size['width'] }}">
                                                    <span class="input-group-text">×&nbsp;@lang('H')</span>
                                                    <input type="number" step="0.1" min="10" class="form-control" name="{{ $name }}[{{ $i }}][height]" value="{{ $size['height'] }}">
                                                    <span class="input-group-text">mm</span>
                                                    <button type="button" class="btn btn-outline-danger remove-size" title="@lang('Remove')"><i class="fas fa-times"></i></button>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary add-size"><i class="fas fa-plus"></i> @lang('Add size')</button>
                                        @break

                                    @default
                                        <label class="form-label fw-semibold" for="{{ $id }}">@lang($setting['label'])</label>
                                        <input class="form-control" id="{{ $id }}" name="{{ $name }}"
                                            @if(in_array($setting['type'], ['int', 'float'], true))
                                                type="number" step="{{ $setting['step'] ?? ($setting['type'] === 'int' ? 1 : 'any') }}"
                                                @isset($setting['min']) min="{{ $setting['min'] }}" @endisset
                                                @isset($setting['max']) max="{{ $setting['max'] }}" @endisset
                                            @else
                                                type="text" maxlength="{{ $setting['max'] ?? 255 }}"
                                            @endif
                                            value="{{ old($error, $setting['value']) }}">
                                @endswitch

                                @if(!empty($setting['help']))
                                    <small class="text-muted d-block mt-1">@lang($setting['help'])</small>
                                @endif
                                @error($error)
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                                @error($error . '.*')
                                    <small class="text-danger d-block">{{ $message }}</small>
                                @enderror
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @component('me::components.btn.save-button', ['text' => __('Save Settings')])
    @endcomponent
</form>
@endsection

@push('js')
<script>
    document.querySelectorAll('.label-sizes').forEach(function (container) {
        const addButton = container.nextElementSibling;
        let index = container.querySelectorAll('.label-size-row').length;

        addButton.addEventListener('click', function () {
            const name = container.dataset.name;
            const row = document.createElement('div');
            row.className = 'input-group input-group-sm mb-1 label-size-row';
            row.innerHTML = `
                <span class="input-group-text">@lang('W')</span>
                <input type="number" step="0.1" min="10" class="form-control" name="${name}[${index}][width]" value="50">
                <span class="input-group-text">×&nbsp;@lang('H')</span>
                <input type="number" step="0.1" min="10" class="form-control" name="${name}[${index}][height]" value="30">
                <span class="input-group-text">mm</span>
                <button type="button" class="btn btn-outline-danger remove-size" title="@lang('Remove')"><i class="fas fa-times"></i></button>`;
            index++;
            container.appendChild(row);
        });

        container.addEventListener('click', function (e) {
            if (e.target.closest('.remove-size') && container.querySelectorAll('.label-size-row').length > 1) {
                e.target.closest('.label-size-row').remove();
            }
        });
    });
</script>
@endpush
