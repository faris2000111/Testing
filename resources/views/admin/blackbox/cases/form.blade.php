<div class="row">
  <div class="col-md-8 mb-3">
    <label class="form-label">Judul Test Case <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
      value="{{ old('title', $case->title ?? '') }}" placeholder="Contoh: Halaman login bisa diakses" required>
    @error('title')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">HTTP Method <span class="text-danger">*</span></label>
    <select name="method" class="form-select @error('method') is-invalid @enderror" required>
      @foreach (['GET', 'POST', 'PUT', 'PATCH', 'DELETE'] as $method)
        <option value="{{ $method }}" {{ old('method', $case->method ?? 'GET') === $method ? 'selected' : '' }}>
          {{ $method }}
        </option>
      @endforeach
    </select>
    @error('method')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-8 mb-3">
    <label class="form-label">Endpoint <span class="text-danger">*</span></label>
    <input type="text" name="endpoint" class="form-control @error('endpoint') is-invalid @enderror"
      value="{{ old('endpoint', $case->endpoint ?? '') }}" placeholder="/login" required>
    @error('endpoint')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Path relatif dari base URL. Contoh: <code>/login</code>, <code>/api/users</code></small>
  </div>

  <div class="col-md-4 mb-3">
    <label class="form-label">Expected Status Code <span class="text-danger">*</span></label>
    <input type="number" name="expected_status" class="form-control @error('expected_status') is-invalid @enderror"
      value="{{ old('expected_status', $case->expected_status ?? 200) }}" min="100" max="599" required>
    @error('expected_status')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-12 mb-3">
    <label class="form-label">Deskripsi</label>
    <textarea name="description" class="form-control @error('description') is-invalid @enderror"
      rows="2" placeholder="Deskripsi singkat tentang test case ini...">{{ old('description', $case->description ?? '') }}</textarea>
    @error('description')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Headers (JSON)</label>
    <textarea name="headers" class="form-control font-monospace @error('headers') is-invalid @enderror"
      rows="3" placeholder='{"Authorization": "Bearer token123"}'>{{ old('headers', isset($case) && $case->headers ? json_encode($case->headers, JSON_PRETTY_PRINT) : '') }}</textarea>
    @error('headers')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Format JSON. Kosongkan jika tidak perlu custom headers.</small>
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Body Parameters (JSON)</label>
    <textarea name="body_params" class="form-control font-monospace @error('body_params') is-invalid @enderror"
      rows="3" placeholder='{"email": "test@test.com", "password": "123456"}'>{{ old('body_params', isset($case) && $case->body_params ? json_encode($case->body_params, JSON_PRETTY_PRINT) : '') }}</textarea>
    @error('body_params')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Format JSON. Untuk request POST/PUT/PATCH.</small>
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Response Harus Mengandung</label>
    <input type="text" name="expected_contains" class="form-control @error('expected_contains') is-invalid @enderror"
      value="{{ old('expected_contains', $case->expected_contains ?? '') }}" placeholder="Contoh: Login berhasil">
    @error('expected_contains')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Teks yang harus ada di response body (case-insensitive).</small>
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Response TIDAK Boleh Mengandung</label>
    <input type="text" name="expected_not_contains" class="form-control @error('expected_not_contains') is-invalid @enderror"
      value="{{ old('expected_not_contains', $case->expected_not_contains ?? '') }}" placeholder="Contoh: Error 500">
    @error('expected_not_contains')
      <div class="invalid-feedback">{{ $message }}</div>
    @enderror
    <small class="text-muted">Teks yang TIDAK boleh ada di response body.</small>
  </div>

  <div class="col-md-6 mb-3">
    <div class="form-check form-switch">
      <input type="hidden" name="is_active" value="0">
      <input class="form-check-input" type="checkbox" name="is_active" value="1" id="is_active"
        {{ old('is_active', $case->is_active ?? true) ? 'checked' : '' }}>
      <label class="form-check-label" for="is_active">Test Case Aktif</label>
    </div>
  </div>
</div>
