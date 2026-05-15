@extends('admin.template.main')

@section('title', 'Website Settings')
@section('page_title', 'Website Settings')

@push('styles')
<style>
  .settings-nav { position: sticky; top: 1rem; }
  @media (max-width: 991.98px) {
    .settings-nav { position: static; }
    .settings-nav .list-group { display: flex; flex-direction: row; flex-wrap: nowrap; overflow-x: auto; gap: 0.4rem; }
    .settings-nav .list-group-item { flex: 0 0 auto; min-width: 10rem; }
  }
  .settings-nav .list-group-item {
    border: 0; border-radius: 0.5rem !important; margin-bottom: 0.25rem;
    background: transparent; color: #344767; font-weight: 500;
    transition: all 0.15s ease; display: flex; align-items: center; gap: 0.65rem; padding: 0.65rem 0.85rem;
  }
  .settings-nav .list-group-item .nav-icon {
    width: 32px; height: 32px; border-radius: 0.5rem;
    display: inline-flex; align-items: center; justify-content: center;
    background: #f0f4ff; color: #4361ee; flex-shrink: 0; font-size: 0.85rem;
  }
  .settings-nav .list-group-item:hover { background: rgba(67, 97, 238, 0.06); color: #4361ee; }
  .settings-nav .list-group-item.active {
    background: linear-gradient(135deg, #4361ee, #3651d4); color: #fff;
    box-shadow: 0 6px 14px rgba(67, 97, 238, 0.25);
  }
  .settings-nav .list-group-item.active .nav-icon { background: rgba(255,255,255,0.18); color: #fff; }
  .settings-section { display: none; }
  .settings-section.is-active { display: block; animation: fadeUp 0.25s ease; }
  @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
  .section-card .card-header { background: transparent; border-bottom: 1px solid #f0f2f5; padding: 1rem 1.25rem; }
  .section-card .card-header h5 { margin: 0; display: flex; align-items: center; gap: 0.6rem; font-size: 1rem; font-weight: 700; }
  .section-card .card-header .header-icon {
    width: 36px; height: 36px; border-radius: 0.6rem;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, var(--icon-from, #4361ee), var(--icon-to, #3651d4)); color: #fff;
  }
  .preview-img { border: 1px solid #e7e9ec; border-radius: 0.5rem; padding: 6px; background: #fff; max-height: 64px; margin-top: 0.5rem; }
  .preview-img.dark { background: #1f2937; }
</style>
@endpush

@section('content')
  @php
    $tabs = [
      ['id' => 'identity', 'icon' => 'fa-id-badge', 'label' => 'Identitas Website'],
      ['id' => 'branding', 'icon' => 'fa-image', 'label' => 'Branding'],
      ['id' => 'contact', 'icon' => 'fa-address-book', 'label' => 'Kontak'],
      ['id' => 'social', 'icon' => 'fa-share-nodes', 'label' => 'Social Media'],
      ['id' => 'seo', 'icon' => 'fa-magnifying-glass-chart', 'label' => 'SEO'],
      ['id' => 'ai', 'icon' => 'fa-robot', 'label' => 'AI Settings'],
      ['id' => 'appearance', 'icon' => 'fa-palette', 'label' => 'Tampilan'],
      ['id' => 'maintenance', 'icon' => 'fa-screwdriver-wrench', 'label' => 'Maintenance'],
    ];
    $activeTab = old('_tab', request()->query('tab', 'identity'));
  @endphp

  <x-admin.page-header
    icon="fa-gear"
    icon-gradient="secondary"
    title="Pengaturan Website"
    description="Konfigurasi identitas, branding, kontak, dan tampilan website."
  />

  <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
    @csrf
    @method('PUT')
    <input type="hidden" name="_tab" id="settingsActiveTab" value="{{ $activeTab }}">

    <div class="row g-4">
      {{-- Sidebar nav --}}
      <div class="col-lg-3">
        <div class="card border-0 shadow-sm settings-nav">
          <div class="card-body p-3">
            <h6 class="text-uppercase text-muted small mb-3 px-2">Pengaturan</h6>
            <div class="list-group list-group-flush" role="tablist">
              @foreach ($tabs as $tab)
                <a href="#tab-{{ $tab['id'] }}" class="list-group-item list-group-item-action {{ $activeTab === $tab['id'] ? 'active' : '' }}" data-settings-tab="{{ $tab['id'] }}">
                  <span class="nav-icon"><i class="fa {{ $tab['icon'] }}"></i></span>
                  <span class="fw-bold small">{{ $tab['label'] }}</span>
                </a>
              @endforeach
            </div>
          </div>
        </div>
      </div>

      {{-- Main panels --}}
      <div class="col-lg-9">

        {{-- Identitas --}}
        <section class="settings-section {{ $activeTab === 'identity' ? 'is-active' : '' }}" id="tab-identity">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#4361ee;--icon-to:#3651d4"><i class="fa fa-id-badge"></i></span> Identitas Website</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Project Name <span class="text-danger">*</span></label>
                  <input type="text" name="project_name" class="form-control @error('project_name') is-invalid @enderror" value="{{ old('project_name', $setting->project_name) }}" required>
                  @error('project_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Site Name <span class="text-danger">*</span></label>
                  <input type="text" name="site_name" class="form-control @error('site_name') is-invalid @enderror" value="{{ old('site_name', $setting->site_name) }}" required>
                  @error('site_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-1">
                  <label class="form-label">Tagline</label>
                  <input type="text" name="tagline" class="form-control @error('tagline') is-invalid @enderror" value="{{ old('tagline', $setting->tagline) }}" placeholder="Your tagline here">
                  @error('tagline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- Branding --}}
        <section class="settings-section {{ $activeTab === 'branding' ? 'is-active' : '' }}" id="tab-branding">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#06b6d4;--icon-to:#0891b2"><i class="fa fa-image"></i></span> Branding</h5>
            </div>
            <div class="card-body">
              <div class="row">
                @foreach ([
                  'logo' => ['Logo', 'logo_url', false],
                  'logo_dark' => ['Logo Dark', 'logo_dark_url', true],
                  'favicon' => ['Favicon', 'favicon_url', false],
                  'apple_touch_icon' => ['Apple Touch Icon', 'apple_touch_icon_url', false],
                ] as $field => $meta)
                  @php [$label, $urlAttr, $isDark] = $meta; @endphp
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ $label }}</label>
                    <input type="file" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" accept="image/*">
                    @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    @if ($setting->{$urlAttr})
                      <img src="{{ $setting->{$urlAttr} }}" alt="{{ $label }}" class="preview-img {{ $isDark ? 'dark' : '' }}">
                    @endif
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </section>

        {{-- Kontak --}}
        <section class="settings-section {{ $activeTab === 'contact' ? 'is-active' : '' }}" id="tab-contact">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#10b981;--icon-to:#059669"><i class="fa fa-address-book"></i></span> Kontak</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label">Email</label>
                  <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $setting->email) }}">
                  @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label">Phone / WhatsApp</label>
                  <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $setting->phone) }}">
                  @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-1">
                  <label class="form-label">Alamat</label>
                  <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="2">{{ old('address', $setting->address) }}</textarea>
                  @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- Social Media --}}
        <section class="settings-section {{ $activeTab === 'social' ? 'is-active' : '' }}" id="tab-social">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#a855f7;--icon-to:#7e22ce"><i class="fa fa-share-nodes"></i></span> Social Media</h5>
            </div>
            <div class="card-body">
              <div class="row">
                @foreach ([
                  'facebook_url' => ['Facebook', 'fa-brands fa-facebook'],
                  'instagram_url' => ['Instagram', 'fa-brands fa-instagram'],
                  'linkedin_url' => ['LinkedIn', 'fa-brands fa-linkedin'],
                  'github_url' => ['GitHub', 'fa-brands fa-github'],
                  'youtube_url' => ['YouTube', 'fa-brands fa-youtube'],
                  'tiktok_url' => ['TikTok', 'fa-brands fa-tiktok'],
                ] as $field => $meta)
                  @php [$label, $icon] = $meta; @endphp
                  <div class="col-md-6 mb-3">
                    <label class="form-label">{{ $label }} URL</label>
                    <div class="input-group">
                      <span class="input-group-text"><i class="{{ $icon }}"></i></span>
                      <input type="url" name="{{ $field }}" class="form-control @error($field) is-invalid @enderror" value="{{ old($field, $setting->{$field}) }}" placeholder="https://...">
                    </div>
                    @error($field)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                  </div>
                @endforeach
              </div>
            </div>
          </div>
        </section>

        {{-- SEO --}}
        <section class="settings-section {{ $activeTab === 'seo' ? 'is-active' : '' }}" id="tab-seo">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#f59e0b;--icon-to:#d97706"><i class="fa fa-magnifying-glass-chart"></i></span> SEO</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-12 mb-3">
                  <label class="form-label">Meta Title</label>
                  <input type="text" name="meta_title" class="form-control @error('meta_title') is-invalid @enderror" value="{{ old('meta_title', $setting->meta_title) }}">
                  @error('meta_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-3">
                  <label class="form-label">Meta Description</label>
                  <textarea name="meta_description" class="form-control @error('meta_description') is-invalid @enderror" rows="3">{{ old('meta_description', $setting->meta_description) }}</textarea>
                  @error('meta_description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12 mb-1">
                  <label class="form-label">Meta Keywords</label>
                  <input type="text" name="meta_keywords" class="form-control @error('meta_keywords') is-invalid @enderror" value="{{ old('meta_keywords', $setting->meta_keywords) }}" placeholder="keyword1, keyword2, keyword3">
                  @error('meta_keywords')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- AI Settings --}}
        <section class="settings-section {{ $activeTab === 'ai' ? 'is-active' : '' }}" id="tab-ai">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#8b5cf6;--icon-to:#6d28d9"><i class="fa fa-robot"></i></span> AI Settings</h5>
            </div>
            <div class="card-body">
              <div class="row">
                {{-- AI Provider Toggle --}}
                <div class="col-12 mb-4">
                  <label class="form-label fw-bold">AI Provider Aktif</label>
                  <p class="text-sm text-muted mb-2">Pilih salah satu provider. Hanya satu yang bisa aktif.</p>
                  @php $currentProvider = old('ai_provider', $setting->ai_provider); @endphp
                  <div class="d-flex gap-3">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="ai_provider" id="ai_gemini" value="gemini"
                             {{ $currentProvider === 'gemini' ? 'checked' : '' }}>
                      <label class="form-check-label fw-bold" for="ai_gemini">
                        <i class="fa fa-gem text-info me-1"></i> Gemini
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="ai_provider" id="ai_openrouter" value="openrouter"
                             {{ $currentProvider === 'openrouter' ? 'checked' : '' }}>
                      <label class="form-check-label fw-bold" for="ai_openrouter">
                        <i class="fa fa-route text-success me-1"></i> OpenRouter
                      </label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" name="ai_provider" id="ai_off" value=""
                             {{ empty($currentProvider) ? 'checked' : '' }}>
                      <label class="form-check-label fw-bold" for="ai_off">
                        <i class="fa fa-power-off text-danger me-1"></i> Nonaktif
                      </label>
                    </div>
                  </div>
                  @error('ai_provider')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                {{-- Gemini API Key --}}
                <div class="col-md-6 mb-3">
                  <label class="form-label">Gemini API Key</label>
                  <input type="password" name="gemini_api_key" class="form-control @error('gemini_api_key') is-invalid @enderror"
                         value="{{ old('gemini_api_key', $setting->gemini_api_key) }}" placeholder="AIza...">
                  <small class="text-muted">Dapatkan dari <a href="https://aistudio.google.com/apikey" target="_blank">Google AI Studio</a></small>
                  @error('gemini_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- OpenRouter API Key --}}
                <div class="col-md-6 mb-3">
                  <label class="form-label">OpenRouter API Key</label>
                  <input type="password" name="openrouter_api_key" class="form-control @error('openrouter_api_key') is-invalid @enderror"
                         value="{{ old('openrouter_api_key', $setting->openrouter_api_key) }}" placeholder="sk-or-...">
                  <small class="text-muted">Dapatkan dari <a href="https://openrouter.ai/keys" target="_blank">OpenRouter</a></small>
                  @error('openrouter_api_key')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- AI System Prompt --}}
                <div class="col-12 mb-1">
                  <label class="form-label">Instruksi AI (System Prompt)</label>
                  <textarea name="ai_system_prompt" class="form-control @error('ai_system_prompt') is-invalid @enderror"
                            rows="5" placeholder="Kamu adalah asisten AI yang membantu...">{{ old('ai_system_prompt', $setting->chatbot_system_prompt) }}</textarea>
                  <small class="text-muted">Instruksi dasar untuk AI. Tentukan perilaku, gaya bahasa, dan batasan AI di sini.</small>
                  @error('ai_system_prompt')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- Tampilan --}}
        <section class="settings-section {{ $activeTab === 'appearance' ? 'is-active' : '' }}" id="tab-appearance">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#f59e0b;--icon-to:#d97706"><i class="fa fa-palette"></i></span> Tampilan</h5>
            </div>
            <div class="card-body">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Navbar Layout</label>
                  <select name="navbar_layout" class="form-select">
                    @php $currentNavbar = old('navbar_layout', $setting->navbar_layout ?? 'classic'); @endphp
                    <option value="classic" {{ $currentNavbar === 'classic' ? 'selected' : '' }}>Classic</option>
                    <option value="minimal" {{ $currentNavbar === 'minimal' ? 'selected' : '' }}>Minimal</option>
                    <option value="branded" {{ $currentNavbar === 'branded' ? 'selected' : '' }}>Branded</option>
                  </select>
                </div>
                <div class="col-md-6 mb-3">
                  <label class="form-label fw-bold">Footer Layout</label>
                  <select name="footer_layout" class="form-select">
                    @php $currentFooter = old('footer_layout', $setting->footer_layout ?? 'classic'); @endphp
                    <option value="classic" {{ $currentFooter === 'classic' ? 'selected' : '' }}>Classic</option>
                    <option value="minimal" {{ $currentFooter === 'minimal' ? 'selected' : '' }}>Minimal</option>
                    <option value="split" {{ $currentFooter === 'split' ? 'selected' : '' }}>Split</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </section>

        {{-- Maintenance --}}
        <section class="settings-section {{ $activeTab === 'maintenance' ? 'is-active' : '' }}" id="tab-maintenance">
          <div class="card section-card border-0 shadow-sm">
            <div class="card-header">
              <h5><span class="header-icon" style="--icon-from:#ef4444;--icon-to:#b91c1c"><i class="fa fa-screwdriver-wrench"></i></span> Maintenance Mode</h5>
            </div>
            <div class="card-body">
              <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="maintenance_mode" value="1" {{ old('maintenance_mode', $setting->maintenance_mode) ? 'checked' : '' }}>
                <label class="form-check-label">Aktifkan mode maintenance (website tidak bisa diakses publik)</label>
              </div>
            </div>
          </div>
        </section>

        {{-- Save button --}}
        <div class="d-flex justify-content-end mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Pengaturan
          </button>
        </div>
      </div>
    </div>
  </form>
@endsection

@push('scripts')
<script>
  // Settings tab navigation
  document.addEventListener('DOMContentLoaded', function () {
    var tabs = document.querySelectorAll('[data-settings-tab]');
    var sections = document.querySelectorAll('.settings-section');
    var hiddenInput = document.getElementById('settingsActiveTab');

    tabs.forEach(function (tab) {
      tab.addEventListener('click', function (e) {
        e.preventDefault();
        var id = this.getAttribute('data-settings-tab');

        tabs.forEach(function (t) { t.classList.remove('active'); });
        this.classList.add('active');

        sections.forEach(function (s) { s.classList.remove('is-active'); });
        var target = document.getElementById('tab-' + id);
        if (target) target.classList.add('is-active');

        if (hiddenInput) hiddenInput.value = id;
      });
    });
  });
</script>
@endpush
