@php $isEdit = isset($section); @endphp

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Nama Section <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $isEdit ? $section->name : '') }}" required placeholder="Contoh: Konten">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Urutan <span class="text-danger">*</span></label>
    <input type="number" name="order" class="form-control @error('order') is-invalid @enderror"
           value="{{ old('order', $isEdit ? $section->order : 0) }}" required min="0" placeholder="0">
    <small class="text-muted">Makin kecil makin atas di sidebar.</small>
    @error('order')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>
</div>
