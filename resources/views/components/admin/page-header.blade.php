<div class="admin-page-header mb-4">
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-3">
    <div class="d-flex align-items-center gap-3">
      <span class="admin-page-header__icon bg-gradient-{{ $iconGradient }}">
        <i class="fa {{ $icon }}"></i>
      </span>
      <div>
        @if ($eyebrow)
          <small class="text-muted text-uppercase fw-bold" style="letter-spacing:0.05em;font-size:0.7rem">{{ $eyebrow }}</small>
        @endif
        <h5 class="mb-0 fw-bold">{{ $title }}</h5>
        @if ($description)
          <p class="text-muted text-sm mb-0">{{ $description }}</p>
        @endif
      </div>
    </div>
    @if ($slot->isNotEmpty())
      <div class="d-flex align-items-center gap-2 flex-wrap">
        {{ $slot }}
      </div>
    @endif
  </div>
</div>
