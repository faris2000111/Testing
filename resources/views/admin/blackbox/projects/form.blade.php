<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Nama Project <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
      value="{{ old('name', $project->name ?? '') }}" placeholder="Contoh: Website Toko Online" required>
    @error('name')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Base URL <span class="text-danger">*</span></label>
    <input type="url" name="base_url" class="form-control @error('base_url') is-invalid @enderror"
      value="{{ old('base_url', $project->base_url ?? '') }}" placeholder="https://example.com" required>
    @error('base_url')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">URL dasar website target (tanpa trailing slash).</small>
  </div>

  <div class="col-12 mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
      rows="3" placeholder="Deskripsi singkat tentang project ini...">{{ old('description', $project->description ?? '') }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <div class="form-check form-switch">
      <input type="hidden" name="is_active" value="0">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
        {{ old('is_active', $project->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Project Aktif</label>
    </div>
  </div>
</div>
