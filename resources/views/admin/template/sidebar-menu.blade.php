{{-- Shared menu items partial (used by both desktop and mobile sidebars) --}}
@forelse ($menuTree as $section => $items)
  <div class="admin-sidenav__section">
    <span class="admin-sidenav__section-title">{{ $section }}</span>

    @foreach ($items as $menu)
      @php
        $hasChildren = $menu->children->isNotEmpty();
        $menuUrl = $menu->resolveUrl();
        $isMenuActive = $menu->isActive();
      @endphp

      @if ($hasChildren)
        @php $collapseId = 'submenu-' . $menu->id . '-' . Str::random(4); @endphp
        <a class="admin-sidenav__link admin-sidenav__link--parent {{ $isMenuActive ? 'is-active' : '' }}"
           data-bs-toggle="collapse"
           href="#{{ $collapseId }}"
           role="button"
           aria-expanded="{{ $isMenuActive ? 'true' : 'false' }}"
           aria-controls="{{ $collapseId }}">
          <span class="admin-sidenav__icon bg-gradient-{{ $menu->icon_gradient }}"><i class="fa {{ $menu->icon }}"></i></span>
          <span class="admin-sidenav__label">{{ $menu->label }}</span>
          <i class="fa fa-chevron-down admin-sidenav__caret"></i>
        </a>
        <div class="collapse admin-sidenav__submenu {{ $isMenuActive ? 'show' : '' }}" id="{{ $collapseId }}">
          @foreach ($menu->children as $child)
            @php $childUrl = $child->resolveUrl(); @endphp
            <a href="{{ $childUrl ?? '#' }}"
               class="admin-sidenav__sublink {{ $child->route_name && request()->routeIs($child->route_name . '*') ? 'is-active' : '' }}"
               @if ($child->url) target="_blank" @endif>
              <span class="admin-sidenav__subdot"></span>
              <span>{{ $child->label }}</span>
            </a>
          @endforeach
        </div>
      @else
        <a href="{{ $menuUrl ?? '#' }}"
           class="admin-sidenav__link {{ $isMenuActive ? 'is-active' : '' }}"
           @if ($menu->url) target="_blank" @endif>
          <span class="admin-sidenav__icon bg-gradient-{{ $menu->icon_gradient }}"><i class="fa {{ $menu->icon }}"></i></span>
          <span class="admin-sidenav__label">{{ $menu->label }}</span>
        </a>
      @endif
    @endforeach
  </div>
@empty
  <div class="admin-sidenav__section">
    <span class="admin-sidenav__section-title">Overview</span>
    <a href="{{ route('admin.dashboard') }}" class="admin-sidenav__link {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
      <span class="admin-sidenav__icon bg-gradient-primary"><i class="fa fa-gauge-high"></i></span>
      <span class="admin-sidenav__label">Dashboard</span>
    </a>
  </div>
  <div class="admin-sidenav__section">
    <span class="admin-sidenav__section-title">Sistem</span>
    <a href="{{ route('admin.settings.edit') }}" class="admin-sidenav__link {{ request()->routeIs('admin.settings.*') ? 'is-active' : '' }}">
      <span class="admin-sidenav__icon bg-gradient-secondary"><i class="fa fa-gear"></i></span>
      <span class="admin-sidenav__label">Pengaturan</span>
    </a>
    <a href="{{ route('admin.menus.index') }}" class="admin-sidenav__link {{ request()->routeIs('admin.menus.*') ? 'is-active' : '' }}">
      <span class="admin-sidenav__icon bg-gradient-info"><i class="fa fa-bars"></i></span>
      <span class="admin-sidenav__label">Menu Manager</span>
    </a>
  </div>
@endforelse
