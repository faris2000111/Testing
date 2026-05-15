<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  @php $siteSetting = \App\Models\SiteSetting::first(); @endphp

  <title>@yield('title', $siteSetting->meta_title ?? $siteSetting->site_name ?? config('app.name'))</title>

  @if ($siteSetting?->meta_description)
    <meta name="description" content="{{ $siteSetting->meta_description }}">
  @endif
  @if ($siteSetting?->meta_keywords)
    <meta name="keywords" content="{{ $siteSetting->meta_keywords }}">
  @endif

  {{-- Favicon --}}
  @if ($siteSetting?->favicon_url)
    <link rel="icon" type="image/png" href="{{ $siteSetting->favicon_url }}">
  @endif
  @if ($siteSetting?->apple_touch_icon_url)
    <link rel="apple-touch-icon" href="{{ $siteSetting->apple_touch_icon_url }}">
  @endif

  {{-- Fonts --}}
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

  {{-- Font Awesome --}}
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

  {{-- Base styles --}}
  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body {
      font-family: 'Inter', sans-serif;
      margin: 0;
      padding: 0;
      color: #1f2937;
      background: #fff;
      line-height: 1.6;
    }
    a { color: #4361ee; text-decoration: none; }
    a:hover { color: #3651d4; }
    img { max-width: 100%; height: auto; }
    .container { width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; }
  </style>

  @stack('styles')
</head>
<body>
  @include('layouts.partials.navbar')

  <main>
    @yield('content')
  </main>

  @include('layouts.partials.footer')

  @stack('scripts')
</body>
</html>
