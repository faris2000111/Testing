<!DOCTYPE html>
<html lang="en">
@include('admin.template.head')
<body class="g-sidenav-show bg-gray-100">
  <div class="min-height-300 bg-dark position-absolute w-100"></div>
  @include('admin.template.sidebar')
  <main class="main-content position-relative border-radius-lg">
    @include('admin.template.navbar')
    <div class="container-fluid py-4">
      @yield('content')
      @include('admin.template.footer')
    </div>
  </main>
  @include('admin.template.scripts')
</body>
</html>
