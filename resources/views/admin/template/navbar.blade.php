<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
  <div class="container-fluid py-1 px-3">
    <nav aria-label="breadcrumb">
      <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
        <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="javascript:;">Admin</a></li>
        <li class="breadcrumb-item text-sm text-white active" aria-current="page">@yield('page_title', 'Dashboard')</li>
      </ol>
      <h6 class="font-weight-bolder text-white mb-0">@yield('page_title', 'Dashboard')</h6>
    </nav>

    {{-- Mobile sidebar toggle --}}
    <a href="javascript:;" id="iconNavbarSidenav" class="nav-link text-white p-0 d-xl-none mobile-sidenav-toggle" aria-label="Buka menu">
      <div class="sidenav-toggler-inner">
        <i class="sidenav-toggler-line"></i>
        <i class="sidenav-toggler-line"></i>
        <i class="sidenav-toggler-line"></i>
      </div>
    </a>

    <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
      <ul class="navbar-nav justify-content-end ms-md-auto align-items-lg-center gap-lg-2">
        <li class="nav-item d-flex align-items-center">
          <button type="button"
                  class="btn btn-link nav-link text-white px-2 admin-theme-toggle mb-0"
                  data-admin-theme-toggle
                  aria-label="Ganti tema gelap/terang"
                  title="Ganti tema (gelap / terang)">
            <i class="fa fa-moon admin-theme-icon admin-theme-icon-moon"></i>
            <i class="fa fa-sun admin-theme-icon admin-theme-icon-sun d-none"></i>
          </button>
        </li>
        @auth
          @php $navAdminUser = auth()->user(); @endphp
          <li class="nav-item dropdown d-flex align-items-center">
            <a class="nav-link text-white d-flex align-items-center gap-2 dropdown-toggle px-2 py-lg-2 mb-0"
               href="#"
               id="navbarUserMenu"
               role="button"
               data-bs-toggle="dropdown"
               data-bs-display="static"
               aria-expanded="false"
               aria-label="Akun Anda">
              <span class="rounded-circle d-inline-flex align-items-center justify-content-center bg-gradient-primary text-white text-xs fw-bold shadow-sm navbar-user-fallback-letter">{{ mb_strtoupper(mb_substr((string) ($navAdminUser->name ?? 'A'), 0, 1)) }}</span>
              <span class="d-none d-xl-inline">{{ $navAdminUser->name ?? 'Administrator' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow px-2 py-3" aria-labelledby="navbarUserMenu" style="max-width: 18rem;">
              <li class="px-3 pb-3 border-bottom">
                <p class="mb-0 text-dark text-sm fw-bold">{{ $navAdminUser->name ?? 'Administrator' }}</p>
                @if ($navAdminUser->email)
                  <span class="text-xs text-muted d-inline-block mt-1" style="word-break: break-all;">{{ $navAdminUser->email }}</span>
                @endif
              </li>
              <li>
                <a class="dropdown-item text-dark py-2 px-3 rounded mb-1" href="{{ route('admin.settings.edit') }}">
                  <i class="fa fa-cog me-2 text-secondary"></i>
                  Pengaturan website
                </a>
              </li>
              <li>
                <a class="dropdown-item text-dark py-2 px-3 rounded mb-1" href="{{ route('admin.password.edit') }}">
                  <i class="fa fa-key me-2 text-secondary"></i>
                  Ubah kata sandi
                </a>
              </li>
              <li>
                <hr class="dropdown-divider my-2 mx-3">
              </li>
              <li>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item text-danger fw-semibold py-2 px-3 rounded mb-0 border-0 bg-transparent text-start w-100">
                    <i class="fa fa-sign-out-alt me-2"></i>
                    Keluar
                  </button>
                </form>
              </li>
            </ul>
          </li>
        @else
          <li class="nav-item d-flex align-items-center">
            <a href="{{ route('login') }}" class="nav-link text-white font-weight-bold px-0">
              <i class="fa fa-user me-sm-1"></i>
              <span class="d-sm-inline d-none">Masuk</span>
            </a>
          </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>
