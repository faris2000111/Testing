{{-- Desktop Sidebar - Fixed left panel, visible on xl+ --}}
<aside class="sidenav admin-sidenav admin-sidenav--desktop navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-4 d-none d-xl-flex" id="sidenav-desktop">
  <div class="admin-sidenav__brand">
    <a class="admin-sidenav__brand-link" href="{{ route('admin.dashboard') }}">
      <span class="admin-sidenav__brand-logo">
        <img src="{{ $siteSetting?->logo_dark_url ?? $siteSetting?->logo_url ?? asset('admin/img/logo-ct-dark.png') }}" alt="logo">
      </span>
      <span class="admin-sidenav__brand-text">
        <strong>{{ $siteSetting?->project_name ?? config('app.name', 'Admin') }}</strong>
        <small>Admin Panel</small>
      </span>
    </a>
    <a class="admin-sidenav__public" href="{{ url('/') }}" target="_blank" title="Buka website publik">
      <i class="fa fa-arrow-up-right-from-square"></i>
    </a>
  </div>

  <div class="admin-sidenav__body">
    <nav class="admin-sidenav__nav" aria-label="Admin navigation desktop">
      @include('admin.template.sidebar-menu')
    </nav>
  </div>

  <div class="admin-sidenav__footer">
    @include('admin.template.sidebar-user')
  </div>
</aside>
