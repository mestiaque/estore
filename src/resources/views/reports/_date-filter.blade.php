@php
    $presets = [
        'today'          => [now()->toDateString(), now()->toDateString()],
        'yesterday'      => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
        'this_week'      => [now()->startOfWeek()->toDateString(), now()->toDateString()],
        'this_month'     => [now()->startOfMonth()->toDateString(), now()->toDateString()],
        'previous_month' => [now()->subMonthNoOverflow()->startOfMonth()->toDateString(), now()->subMonthNoOverflow()->endOfMonth()->toDateString()],
        'this_year'      => [now()->startOfYear()->toDateString(), now()->toDateString()],
    ];
@endphp

<div class="card glass-card w-100 mb-3">
    <div class="card-body">
        <div class="d-flex flex-wrap gap-1 mb-2">
            @foreach($presets as $key => $range)
                <a href="{{ request()->fullUrlWithQuery(['from' => $range[0], 'to' => $range[1]]) }}"
                    class="btn btn-sm {{ $from == $range[0] && $to == $range[1] ? 'btn-encodex-active' : 'btn-encodex-list' }}">
                    @lang(ucfirst(str_replace('_', ' ', $key)))
                </a>
            @endforeach
        </div>
        <form method="GET" class="row g-2 align-items-end">
            @foreach(request()->except(['from', 'to', '_token']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <div class="col-auto">
                <label class="form-label mb-0">@lang('From')</label>
                <input type="date" name="from" class="form-control form-control-sm" value="{{ $from }}">
            </div>
            <div class="col-auto">
                <label class="form-label mb-0">@lang('To')</label>
                <input type="date" name="to" class="form-control form-control-sm" value="{{ $to }}">
            </div>
            {{ $extra ?? '' }}
            <div class="col-auto">
                <button type="submit" class="btn btn-sm btn-encodex-search">@lang('Filter')</button>
            </div>
        </form>
    </div>
</div>
