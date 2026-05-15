@php
  $isEdit = isset($menu);
  $gradients = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'dark'];
@endphp

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Label <span class="text-danger">*</span></label>
    <input type="text" name="label" class="form-control @error('label') is-invalid @enderror"
           value="{{ old('label', $isEdit ? $menu->label : '') }}" required placeholder="Products"
           id="menuLabel">
    @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  @if (! $isEdit)
    <div class="col-md-6 mb-3">
      <label class="form-label">Slug (ID Menu) <span class="text-danger">*</span></label>
      <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
             value="{{ old('slug') }}" required placeholder="products"
             pattern="[a-z0-9\-]+" id="menuSlug">
      <small class="text-muted">Huruf kecil, angka, dan strip saja. Ini jadi nama folder view & route.</small>
      @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
  @else
    <div class="col-md-6 mb-3">
      <label class="form-label">Slug</label>
      <input type="text" class="form-control" value="{{ $menu->slug }}" disabled>
      <small class="text-muted">Slug tidak bisa diubah setelah dibuat.</small>
    </div>
  @endif

  <div class="col-md-6 mb-3">
    <label class="form-label">Section <span class="text-danger">*</span></label>
    <select name="section_id" class="form-select @error('section_id') is-invalid @enderror" required>
      <option value="">— Pilih Section —</option>
      @foreach ($sections ?? [] as $s)
        <option value="{{ $s->id }}" {{ old('section_id', $isEdit ? $menu->section_id : '') == $s->id ? 'selected' : '' }}>
          {{ $s->name }} (urutan: {{ $s->order }})
        </option>
      @endforeach
    </select>
    <small class="text-muted">Grup di sidebar. <a href="{{ route('admin.sections.create') }}" target="_blank">Buat section baru</a></small>
    @error('section_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Icon (Font Awesome) <span class="text-danger">*</span></label>
    <div class="input-group">
      <span class="input-group-text" id="iconPreview"><i class="fa {{ old('icon', $isEdit ? $menu->icon : 'fa-circle') }}"></i></span>
      <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror"
             value="{{ old('icon', $isEdit ? $menu->icon : 'fa-circle') }}" required
             placeholder="fa-box" id="iconInput">
    </div>
    <small class="text-muted">Contoh: fa-box, fa-users, fa-newspaper, fa-chart-line</small>
    @error('icon')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Icon Gradient</label>
    <select name="icon_gradient" class="form-select @error('icon_gradient') is-invalid @enderror">
      @foreach ($gradients as $g)
        <option value="{{ $g }}" {{ old('icon_gradient', $isEdit ? $menu->icon_gradient : 'primary') === $g ? 'selected' : '' }}>
          {{ ucfirst($g) }}
        </option>
      @endforeach
    </select>
    @error('icon_gradient')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Parent Menu</label>
    <select name="parent_id" class="form-select @error('parent_id') is-invalid @enderror">
      <option value="">— Root (tidak punya parent) —</option>
      @foreach ($parentMenus ?? [] as $p)
        <option value="{{ $p->id }}" {{ old('parent_id', $isEdit ? $menu->parent_id : '') == $p->id ? 'selected' : '' }}>
          {{ $p->label }} ({{ $p->section->name ?? '-' }})
        </option>
      @endforeach
    </select>
    @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  @if (! $isEdit)
    <div class="col-md-6 mb-3 d-flex align-items-end">
      <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="has_crud" value="1"
               {{ old('has_crud') ? 'checked' : '' }} id="hasCrudCheck">
        <label class="form-check-label" for="hasCrudCheck">
          <strong>Generate CRUD</strong> (create, edit, form)
        </label>
      </div>
    </div>
  @endif

  <div class="col-md-6 mb-3 d-flex align-items-end">
    <div class="form-check form-switch">
      <input class="form-check-input" type="checkbox" name="is_active" value="1"
             {{ old('is_active', $isEdit ? $menu->is_active : true) ? 'checked' : '' }}>
      <label class="form-check-label">Aktif (tampil di sidebar)</label>
    </div>
  </div>
</div>

@if (! $isEdit)
  <div class="alert alert-light border mt-2">
    <i class="fa fa-magic me-1"></i>
    <strong>Auto-generate:</strong> Saat disimpan, sistem akan otomatis membuat:
    <ul class="mb-0 mt-1 small">
      <li>Controller: <code>app/Http/Controllers/Admin/{Slug}Controller.php</code></li>
      <li>View: <code>resources/views/admin/{slug}/index.blade.php</code></li>
      <li class="crud-note d-none">View: <code>resources/views/admin/{slug}/create.blade.php</code></li>
      <li class="crud-note d-none">View: <code>resources/views/admin/{slug}/edit.blade.php</code></li>
      <li class="crud-note d-none">View: <code>resources/views/admin/{slug}/form.blade.php</code></li>
      <li>Route: <code>admin/{slug}</code> → otomatis terdaftar</li>
    </ul>
  </div>
@endif

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    // Icon preview
    var iconInput = document.getElementById('iconInput');
    var iconPreview = document.getElementById('iconPreview');
    if (iconInput && iconPreview) {
      iconInput.addEventListener('input', function () {
        iconPreview.innerHTML = '<i class="fa ' + this.value + '"></i>';
      });
    }

    // Auto-generate slug from label
    var labelInput = document.getElementById('menuLabel');
    var slugInput = document.getElementById('menuSlug');
    if (labelInput && slugInput) {
      labelInput.addEventListener('input', function () {
        if (!slugInput.dataset.manual) {
          slugInput.value = this.value
            .toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
        }
      });
      slugInput.addEventListener('input', function () {
        this.dataset.manual = '1';
      });
    }

    // Show/hide CRUD notes
    var crudCheck = document.getElementById('hasCrudCheck');
    var crudNotes = document.querySelectorAll('.crud-note');
    if (crudCheck) {
      function toggleCrud() {
        crudNotes.forEach(function (el) {
          el.classList.toggle('d-none', !crudCheck.checked);
        });
      }
      crudCheck.addEventListener('change', toggleCrud);
      toggleCrud();
    }
  });
</script>
@endpush
