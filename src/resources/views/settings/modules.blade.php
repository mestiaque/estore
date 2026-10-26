@extends('me::master')

@section('title', trans('Retail Configuration'))

@push('css')
<style>
    .feature-group { border-radius: .75rem; }
    .feature-row { display: flex; gap: .85rem; align-items: flex-start; padding: .75rem .25rem; border-bottom: 1px solid var(--bs-border-color-translucent); }
    .feature-row:last-child { border-bottom: 0; }
    .feature-row .form-switch { padding-left: 2.75em; margin: 0; min-height: auto; }
    .feature-row .form-switch .form-check-input { width: 2.5em; height: 1.35em; margin-left: -2.75em; cursor: pointer; }
    .feature-row.is-inactive .feature-title { color: var(--bs-secondary-color); }
    .feature-title { font-weight: 600; }
    .feature-needs { font-size: .75rem; }
</style>
@endpush

@section('content')
@include('retail::settings._nav')

<p class="text-muted small mb-3">
    @lang('Switch any part of the system on or off. Turned-off features disappear from the menu and screens; nothing already saved is deleted.')
</p>

<form action="{{ route('retail.settings.modules.update') }}" method="POST" id="configuration-form">
    @csrf
    @method('PUT')

    <div class="row">
        @foreach($groups as $groupKey => $groupLabel)
            @continue(!isset($features[$groupKey]))
            <div class="col-xl-6 mb-3">
                <div class="card glass-card feature-group h-100">
                    <div class="card-header"><b>@lang($groupLabel)</b></div>
                    <div class="card-body py-1">
                        @foreach($features[$groupKey] as $feature)
                            <div class="feature-row {{ $feature['on'] && !$feature['active'] ? 'is-inactive' : '' }}" data-feature="{{ $feature['key'] }}" data-requires="{{ implode(',', $feature['requires']) }}">
                                <div class="form-check form-switch">
                                    <input class="form-check-input feature-switch" type="checkbox" role="switch"
                                           id="feature_{{ $feature['key'] }}" name="features[{{ $feature['key'] }}]" value="1"
                                           {{ $feature['on'] ? 'checked' : '' }}>
                                </div>
                                <div class="flex-grow-1">
                                    <label for="feature_{{ $feature['key'] }}" class="feature-title mb-0" style="cursor:pointer">@lang($feature['label'])</label>
                                    <small class="text-muted d-block">@lang($feature['description'])</small>
                                    @if($feature['requires'])
                                        <span class="feature-needs text-muted">
                                            <i class="fas fa-link"></i> @lang('Needs'):
                                            {{ collect($feature['requires'])->map(fn ($r) => __($definitions[$r]['label'] ?? $r))->implode(', ') }}
                                        </span>
                                    @endif

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @component('me::components.btn.save-button', ['text' => __('Save Configuration')])
    @endcomponent
</form>
@endsection

@push('js')
<script>
    // Greys out a feature whose required feature is switched off, so it is clear it won't take effect.
    (function () {
        const rows = Array.from(document.querySelectorAll('.feature-row'));
        const isOn = key => document.getElementById(`feature_${key}`)?.checked;

        function isActive(key, seen = []) {
            const row = rows.find(r => r.dataset.feature === key);
            if (!row || seen.includes(key) || !isOn(key)) return false;
            return (row.dataset.requires ? row.dataset.requires.split(',') : []).every(r => isActive(r, [...seen, key]));
        }

        function refresh() {
            rows.forEach(row => {
                row.classList.toggle('is-inactive', isOn(row.dataset.feature) && !isActive(row.dataset.feature));
            });
        }

        document.querySelectorAll('.feature-switch').forEach(input => input.addEventListener('change', refresh));
        refresh();
    })();
</script>
@endpush
