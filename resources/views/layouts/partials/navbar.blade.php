@php $siteSetting = $siteSetting ?? \App\Models\SiteSetting::first(); @endphp

<header style="
  position: sticky; top: 0; z-index: 100;
  background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);
  border-bottom: 1px solid #f0f0f0;
  padding: 0.75rem 0;
">
  <div class="container" style="display: flex; align-items: center; justify-content: space-between;">
    {{-- Logo / Brand --}}
    <a href="{{ url('/') }}" style="display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 1.1rem; color: #1f2937;">
      @if ($siteSetting?->logo_url)
        <img src="{{ $siteSetting->logo_url }}" alt="{{ $siteSetting->site_name }}" style="height: 32px;">
      @else
        {{ $siteSetting->site_name ?? config('app.name') }}
      @endif
    </a>

    {{-- Navigation --}}
    <nav style="display: flex; align-items: center; gap: 1.5rem;">
      @yield('nav-items')
      {{-- Default nav items - override in child views --}}
      @hasSection('nav-items')
      @else
        <a href="{{ url('/') }}" style="font-size: 0.9rem; font-weight: 500; color: #4b5563;">Home</a>
      @endif
    </nav>
  </div>
</header>
