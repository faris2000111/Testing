<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ $siteSetting?->apple_touch_icon_url ?? asset('admin/img/apple-icon.png') }}">
  <link rel="icon" type="image/png" href="{{ $siteSetting?->favicon_url ?? asset('admin/img/favicon.png') }}">
  <title>@yield('title', ($siteSetting?->project_name ? $siteSetting->project_name . ' Admin' : config('app.name', 'Admin') . ' Dashboard'))</title>
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <link href="{{ asset('admin/css/nucleo-icons.css') }}" rel="stylesheet" />
  <link href="{{ asset('admin/css/nucleo-svg.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
  <link id="pagestyle" href="{{ asset('admin/css/argon-dashboard.css') }}" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" />
  <link href="{{ asset('admin/css/admin-datatables.css') }}" rel="stylesheet" />
  <link href="{{ asset('admin/css/admin-ui.css') }}" rel="stylesheet" />
  <script>
    // Apply admin theme early to avoid FOUC
    (function () {
      try {
        var stored = window.localStorage.getItem('admin_theme');
        var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        var theme = (stored === 'dark' || stored === 'light') ? stored : (prefersDark ? 'dark' : 'light');
        document.documentElement.setAttribute('data-admin-theme', theme);
        document.addEventListener('DOMContentLoaded', function () {
          document.body.setAttribute('data-theme', theme);
        });
      } catch (e) {}
    })();
  </script>
  @stack('styles')
</head>
