{{-- Mobile Sidebar - Slide-in overlay, visible on <xl --}}
<aside class="sidenav admin-sidenav admin-sidenav--mobile navbar navbar-vertical navbar-expand-xs border-0 fixed-start d-xl-none" id="sidenav-mobile">
  <div class="admin-sidenav__brand">
    <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0" aria-hidden="true" id="iconSidenav"></i>
    <a class="admin-sidenav__brand-link" href="{{ route('admin.dashboard') }}">
      <span class="admin-sidenav__brand-logo">
        <img src="{{ $siteSetting?->logo_dark_url ?? $siteSetting?->logo_url ?? asset('admin/img/logo-ct-dark.png') }}" alt="logo">
      </span>
      <span class="admin-sidenav__brand-text">
        <strong>{{ $siteSetting?->project_name ?? config('app.name', 'Admin') }}</strong>
        <small>Admin Panel</small>
      </span>
    </a>
  </div>

  <div class="admin-sidenav__body">
    <nav class="admin-sidenav__nav" aria-label="Admin navigation mobile">
      @include('admin.template.sidebar-menu')
    </nav>
  </div>

  <div class="admin-sidenav__footer">
    @include('admin.template.sidebar-user')
  </div>
</aside>
