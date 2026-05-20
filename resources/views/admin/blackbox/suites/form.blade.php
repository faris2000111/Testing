<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Nama Suite <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
      value="{{ old('name', $suite->name ?? '') }}" placeholder="Contoh: Login Tests, Dashboard Tests" required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Deskripsi</label>
    <input type="text" name="description" class="form-control @error('description') is-invalid @enderror"
      value="{{ old('description', $suite->description ?? '') }}" placeholder="Deskripsi singkat suite ini">
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <div class="form-check form-switch">
      <input type="hidden" name="is_active" value="0">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
        {{ old('is_active', $suite->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Suite Aktif</label>
    </div>
  </div>
</div>
