@extends('me::master')

@section('title', trans('Business Type'))

@section('content')
@include('retail::settings._nav')

<div class="card glass-card w-100">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <b>@lang('What kind of shop is this?')</b>
        <div class="d-flex gap-1">
            @can('retail_setting.edit')
                <a href="{{ route('retail.settings.business-types.create') }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-plus"></i> @lang('Add Preset')</a>
                @if($customised)
                    <button type="submit" form="reset-presets-form" class="btn btn-sm btn-outline-secondary" onclick="return confirm(@json(__('Restore the original presets? Your edited and added presets will be removed.')))">
                        <i class="fas fa-undo"></i> @lang('Reset to Defaults')
                    </button>
                @endif
            @endcan
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            @lang('Choosing a type sets sensible defaults: which features are on, how new products are tracked, and the common units. Existing products, stock and invoices are not changed, and every feature can still be switched individually on the Features tab.')
        </p>
        <form action="{{ route('retail.settings.business-type.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                @foreach($types as $key => $type)
                    <div class="col-md-6 col-xl-4 mb-3">
                        <label class="card h-100 p-3 {{ $current === $key ? 'border-primary' : '' }}" for="business_type_{{ $key }}" style="cursor:pointer">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="business_type" id="business_type_{{ $key }}" value="{{ $key }}" {{ old('business_type', $current) === $key ? 'checked' : '' }} required>
                                    <b>@lang($type['label'])</b>
                                    @if($current === $key)
                                        <span class="badge bg-primary ms-1">@lang('Current')</span>
                                    @endif
                                </div>
                                @can('retail_setting.edit')
                                <div class="text-nowrap">
                                    <a href="{{ route('retail.settings.business-types.edit', $key) }}" class="btn btn-sm btn-link p-0 me-2" title="@lang('Edit')"><i class="fas fa-pen"></i></a>
                                    @if($current !== $key)
                                        <button type="submit" form="delete-preset-{{ $key }}" class="btn btn-sm btn-link text-danger p-0" title="@lang('Delete')"
                                                onclick="return confirm(@json(__('Delete this preset?')))"><i class="fas fa-trash"></i></button>
                                    @endif
                                </div>
                                @endcan
                            </div>
                            <small class="text-muted d-block mb-2">@lang($type['description'] ?? '')</small>
                            <small class="d-block">
                                <b>@lang('New products'):</b>
                                {{ __(ucfirst($type['track_type'])) }}{{ ($type['has_expiry'] ?? false) ? ', ' . __('with expiry') : '' }}
                            </small>
                            <small class="d-block">
                                <b>@lang('Turned off'):</b>
                                @forelse($type['disabled'] ?? [] as $feature)
                                    <span class="badge bg-secondary">@lang($features[$feature]['label'] ?? $feature)</span>
                                @empty
                                    <span class="badge bg-success">@lang('Nothing — everything on')</span>
                                @endforelse
                            </small>
                            <small class="d-block">
                                <b>@lang('Units'):</b>
                                {{ collect($type['units'] ?? [])->map(fn ($unit) => $unitCatalog[$unit]['short_name'] ?? $unit)->implode(', ') ?: '-' }}
                            </small>
                        </label>
                    </div>
                @endforeach
            </div>

            @component('me::components.btn.save-button', ['text' => __('Apply Business Type')])
            @endcomponent
        </form>
    </div>
</div>

{{-- Delete/reset forms live outside the apply form; the buttons above target them with form="…". --}}
@can('retail_setting.edit')
    @foreach($types as $key => $type)
        <form id="delete-preset-{{ $key }}" action="{{ route('retail.settings.business-types.destroy', $key) }}" method="POST" class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endforeach
    <form id="reset-presets-form" action="{{ route('retail.settings.business-types.reset') }}" method="POST" class="d-none">
        @csrf
    </form>
@endcan
@endsection
