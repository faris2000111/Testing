@php
    $isActive = fn (string ...$patterns) => collect($patterns)->contains(fn ($p) => request()->routeIs($p));
    $sitePublicUrl = url('/');
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

      {{-- ===== Overview ===== --}}
      <div class="admin-sidenav__section">
        <span class="admin-sidenav__section-title">Overview</span>

        <a href="{{ route('admin.dashboard') }}"
           class="admin-sidenav__link {{ $isActive('admin.dashboard') ? 'is-active' : '' }}">
          <span class="admin-sidenav__icon bg-gradient-primary"><i class="fa fa-gauge-high"></i></span>
          <span class="admin-sidenav__label">Dashboard</span>
        </a>
      </div>

      {{-- ===== Sistem ===== --}}
      <div class="admin-sidenav__section">
        <span class="admin-sidenav__section-title">Sistem</span>

        <a href="{{ route('admin.settings.edit') }}"
           class="admin-sidenav__link {{ $isActive('admin.settings.*') ? 'is-active' : '' }}">
          <span class="admin-sidenav__icon bg-gradient-secondary"><i class="fa fa-gear"></i></span>
          <span class="admin-sidenav__label">Pengaturan</span>
        </a>

        <a href="{{ route('admin.password.edit') }}"
           class="admin-sidenav__link {{ $isActive('admin.password.*') ? 'is-active' : '' }}">
          <span class="admin-sidenav__icon bg-gradient-warning"><i class="fa fa-key"></i></span>
          <span class="admin-sidenav__label">Ubah Password</span>
        </a>
      </div>
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
