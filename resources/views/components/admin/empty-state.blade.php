<div class="text-center py-5 px-3">
  <i class="fa {{ $icon }} fa-2x text-muted mb-3 d-block"></i>
  <h6 class="text-muted mb-1">{{ $title }}</h6>
  @if ($description)
    <p class="text-muted text-sm mb-0">{{ $description }}</p>
  @endif
</div>
