@php $isEdit = isset($user); @endphp

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
           value="{{ old('name', $isEdit ? $user->name : '') }}" required placeholder="John Doe">
    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Username <span class="text-danger">*</span></label>
    <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
           value="{{ old('username', $isEdit ? $user->username : '') }}" required placeholder="johndoe"
           pattern="[a-z0-9\-_]+">
    <small class="text-muted">Huruf kecil, angka, strip, underscore saja.</small>
    @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
           value="{{ old('email', $isEdit ? $user->email : '') }}" placeholder="john@example.com">
    <small class="text-muted">Opsional.</small>
    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Role <span class="text-danger">*</span></label>
    <select name="role_id" class="form-select @error('role_id') is-invalid @enderror" required>
      <option value="">— Pilih Role —</option>
      @foreach ($roles as $role)
        <option value="{{ $role->id }}" {{ old('role_id', $isEdit ? $user->role_id : '') == $role->id ? 'selected' : '' }}>
          {{ $role->label }} {{ $role->is_superadmin ? '(Superadmin)' : '' }}
        </option>
      @endforeach
    </select>
    @error('role_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Password {{ $isEdit ? '' : '*' }}</label>
    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror"
           {{ $isEdit ? '' : 'required' }} placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Min. 6 karakter' }}">
    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  <div class="col-md-6 mb-3">
    <label class="form-label">Konfirmasi Password {{ $isEdit ? '' : '*' }}</label>
    <input type="password" name="password_confirmation" class="form-control"
           {{ $isEdit ? '' : 'required' }} placeholder="Ulangi password">
  </div>
</div>
