@php
    use App\Models\AdminMenu;
    use Illuminate\Support\Facades\Schema;

    $sitePublicUrl = url('/');

    // Load dynamic menus from database, filtered by user role
    $menuTree = Schema::hasTable('admin_menus') ? AdminMenu::getMenuTree(auth()->user()) : [];
@endphp

<aside class="sidenav admin-sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4" id="sidenav-main">
  <div class="admin-sidenav__brand">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
    <a class="admin-sidenav__brand-link" href="{{ route('admin.dashboard') }}">
      <span class="admin-sidenav__brand-logo">
        <img src="{{ $siteSetting?->logo_dark_url ?? $siteSetting?->logo_url ?? asset('admin/img/logo-ct-dark.png') }}" alt="logo">
      </span>
      <span class="admin-sidenav__brand-text">
        <strong>{{ $siteSetting?->project_name ?? config('app.name', 'Admin') }}</strong>
        <small>Admin Panel</small>
      </span>
    </a>
    <a class="admin-sidenav__public" href="{{ $sitePublicUrl }}" target="_blank" title="Buka website publik">
      <i class="fa fa-arrow-up-right-from-square"></i>
    </a>
  </div>

  <div class="admin-sidenav__body">
    <nav class="admin-sidenav__nav" aria-label="Admin navigation">

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
              {{-- Parent with submenu --}}
              @php $collapseId = 'submenu-' . $menu->id; @endphp
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
              {{-- Single link --}}
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
        {{-- Fallback jika belum ada menu di database --}}
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

    </nav>
  </div>

  <div class="admin-sidenav__footer">
    @auth
      @php $user = auth()->user(); @endphp
      <div class="admin-sidenav__user">
        <span class="admin-sidenav__user-avatar">
          {{ mb_strtoupper(mb_substr((string) ($user->name ?: $user->email), 0, 1)) }}
        </span>
        <div class="admin-sidenav__user-meta">
          <strong>{{ $user->name ?? 'Administrator' }}</strong>
          @if ($user->email)
            <small>{{ $user->email }}</small>
          @endif
        </div>
      </div>
      <form action="{{ route('logout') }}" method="POST" class="m-0 mt-2">
        @csrf
        <button type="submit" class="admin-sidenav__logout">
          <i class="fa fa-right-from-bracket me-1"></i> Keluar
        </button>
      </form>
    @endauth
  </div>
</aside>
