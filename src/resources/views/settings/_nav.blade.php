{{-- Shared tab bar for the Retail configuration screens. --}}
<ul class="nav nav-pills mb-3 gap-1 flex-wrap">
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('retail.settings.business-type*', 'retail.settings.business-types.*') ? 'active' : '' }}" href="{{ route('retail.settings.business-type.edit') }}">
            <i class="fas fa-store"></i> @lang('Business Type')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('retail.settings.modules.*') ? 'active' : '' }}" href="{{ route('retail.settings.modules.edit') }}">
            <i class="fas fa-toggle-on"></i> @lang('Features')
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('retail.settings.general.*') ? 'active' : '' }}" href="{{ route('retail.settings.general.edit') }}">
            <i class="fas fa-sliders-h"></i> @lang('Settings')
        </a>
    </li>
</ul>
