@php $isEdit = isset($role); @endphp

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Label (Nama Tampilan) <span class="text-danger">*</span></label>
    <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
           value="{{ old('label', $isEdit ? $role->label : '') }}" required placeholder="Contoh: Editor">
    @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Name (Slug) <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $isEdit ? $role->name : '') }}" {{ $isEdit ? 'readonly' : 'required' }}
           placeholder="Contoh: editor" pattern="[a-z0-9\-_]+">
    <small class="text-muted">Huruf kecil, angka, strip, underscore saja. Tidak bisa diubah setelah dibuat.</small>
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" id="is_superadmin" name="is_superadmin" value="1"
             {{ old('is_superadmin', $isEdit ? $role->is_superadmin : false) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_superadmin">
        <strong>Superadmin</strong> — otomatis dapat akses semua menu
      </label>
    </div>
  </div>
</div>

<hr class="my-3">

<div id="menu-access-section">
  <h6 class="mb-3"><i class="fa fa-bars me-2"></i>Hak Akses Menu</h6>
  <p class="text-sm text-muted mb-3">Centang menu yang bisa diakses oleh role ini. Jika Superadmin diaktifkan, semua menu otomatis bisa diakses.</p>

  @php
    $assignedIds = old('menus', $isEdit ? ($assignedMenuIds ?? []) : []);
  @endphp

  <div class="row">
    @foreach ($menus as $menu)
      <div class="col-md-6 col-lg-4 mb-2">
        <div class="form-check">
          <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" value="{{ $menu->id }}"
                 id="menu_{{ $menu->id }}" {{ in_array($menu->id, $assignedIds) ? 'checked' : '' }}>
          <label class="form-check-label" for="menu_{{ $menu->id }}">
            <i class="fa {{ $menu->icon }} text-{{ $menu->icon_gradient }} me-1"></i>
            <strong>{{ $menu->label }}</strong>
            <small class="text-muted">({{ $menu->section->name ?? '-' }})</small>
          </label>
        </div>

        {{-- Children --}}
        @if ($menu->children->isNotEmpty())
          <div class="ms-4 mt-1">
            @foreach ($menu->children as $child)
              <div class="form-check">
                <input class="form-check-input menu-checkbox" type="checkbox" name="menus[]" value="{{ $child->id }}"
                       id="menu_{{ $child->id }}" {{ in_array($child->id, $assignedIds) ? 'checked' : '' }}>
                <label class="form-check-label" for="menu_{{ $child->id }}">
                  {{ $child->label }}
                </label>
              </div>
            @endforeach
          </div>
        @endif
      </div>
    @endforeach
  </div>
</div>

@push('scripts')
<script>
  // Toggle menu checkboxes when superadmin is toggled
  document.getElementById('is_superadmin').addEventListener('change', function() {
    const section = document.getElementById('menu-access-section');
    const checkboxes = section.querySelectorAll('.menu-checkbox');

    if (this.checked) {
      checkboxes.forEach(cb => { cb.checked = true; cb.disabled = true; });
      section.style.opacity = '0.5';
    } else {
      checkboxes.forEach(cb => { cb.disabled = false; });
      section.style.opacity = '1';
    }
  });

  // Trigger on page load
  document.getElementById('is_superadmin').dispatchEvent(new Event('change'));
</script>
@endpush
