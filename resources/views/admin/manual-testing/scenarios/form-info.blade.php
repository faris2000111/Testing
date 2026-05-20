<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Judul Scenario <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
      value="{{ old('title', $scenario->title ?? '') }}" placeholder="Contoh: Login dengan kredensial valid" required>
    @error('title')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">Module</label>
    <input type="text" name="module" class="form-control @error('module') is-invalid @enderror"
      value="{{ old('module', $scenario->module ?? '') }}" placeholder="Contoh: Login, Checkout">
    @error('module')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-3 mb-3">
    <label class="form-label">Priority <span class="text-danger">*</span></label>
    <select name="priority" class="form-select @error('priority') is-invalid @enderror" required>
      @foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label)
        <option value="{{ $val }}" {{ old('priority', $scenario->priority ?? 'medium') === $val ? 'selected' : '' }}>
          {{ $label }}
        </option>
      @endforeach
    </select>
    @error('priority')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
      rows="3" placeholder="Deskripsi singkat tentang scenario ini...">{{ old('description', $scenario->description ?? '') }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Precondition</label>
    <textarea name="precondition" class="form-control @error('precondition') is-invalid @enderror"
      rows="3" placeholder="Kondisi yang harus terpenuhi sebelum testing...">{{ old('precondition', $scenario->precondition ?? '') }}</textarea>
    @error('precondition')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Contoh: User sudah terdaftar, browser sudah login, dll.</small>
  </div>

  <div class="col-md-6 mb-3">
    <div class="form-check form-switch">
      <input type="hidden" name="is_active" value="0">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
        {{ old('is_active', $scenario->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Scenario Aktif</label>
    </div>
  </div>
</div>
