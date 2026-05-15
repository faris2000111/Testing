@php $siteSetting = $siteSetting ?? \App\Models\SiteSetting::first(); @endphp

<footer style="
  background: #1f2937; color: rgba(255,255,255,0.7);
  padding: 3rem 0 1.5rem; margin-top: 4rem;
">
  <div class="container">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem; margin-bottom: 2rem;">
      {{-- Brand --}}
      <div>
        <h4 style="color: #fff; font-size: 1.1rem; margin-bottom: 0.75rem;">
          {{ $siteSetting->site_name ?? config('app.name') }}
        </h4>
        @if ($siteSetting?->tagline)
          <p style="font-size: 0.85rem; line-height: 1.6;">{{ $siteSetting->tagline }}</p>
        @endif
      </div>

      {{-- Contact --}}
      @if ($siteSetting?->email || $siteSetting?->phone)
        <div>
          <h5 style="color: #fff; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Kontak</h5>
          @if ($siteSetting->email)
            <p style="font-size: 0.85rem; margin-bottom: 0.25rem;">
              <i class="fa fa-envelope" style="width: 16px;"></i> {{ $siteSetting->email }}
            </p>
          @endif
          @if ($siteSetting->phone)
            <p style="font-size: 0.85rem;">
              <i class="fa fa-phone" style="width: 16px;"></i> {{ $siteSetting->phone }}
            </p>
          @endif
        </div>
      @endif

      {{-- Social --}}
      @php
        $socials = collect([
          ['url' => $siteSetting?->facebook_url, 'icon' => 'fa-brands fa-facebook'],
          ['url' => $siteSetting?->instagram_url, 'icon' => 'fa-brands fa-instagram'],
          ['url' => $siteSetting?->linkedin_url, 'icon' => 'fa-brands fa-linkedin'],
          ['url' => $siteSetting?->github_url, 'icon' => 'fa-brands fa-github'],
          ['url' => $siteSetting?->youtube_url, 'icon' => 'fa-brands fa-youtube'],
          ['url' => $siteSetting?->tiktok_url, 'icon' => 'fa-brands fa-tiktok'],
        ])->filter(fn ($s) => ! empty($s['url']));
      @endphp
      @if ($socials->isNotEmpty())
        <div>
          <h5 style="color: #fff; font-size: 0.9rem; margin-bottom: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em;">Social</h5>
          <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            @foreach ($socials as $social)
              <a href="{{ $social['url'] }}" target="_blank" rel="noopener"
                 style="width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,0.1); display: inline-flex; align-items: center; justify-content: center; color: #fff; transition: background 0.2s;"
                 onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="{{ $social['icon'] }}"></i>
              </a>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin-bottom: 1rem;">
    <p style="font-size: 0.8rem; text-align: center; color: rgba(255,255,255,0.5);">
      &copy; {{ date('Y') }} {{ $siteSetting->site_name ?? config('app.name') }}. All rights reserved.
    </p>
  </div>
</footer>
