@extends('admin.template.main')

@section('title', 'Ubah Password')
@section('page_title', 'Ubah Password')

@section('content')
  <x-admin.page-header
    icon="fa-key"
    icon-gradient="warning"
    title="Ubah Kata Sandi"
    description="Ganti kata sandi akun admin kamu."
  />

  <div class="row">
    <div class="col-lg-6">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form action="{{ route('admin.password.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
              <label class="form-label">Password Saat Ini <span class="text-danger">*</span></label>
              <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
              @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Password Baru <span class="text-danger">*</span></label>
              <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
              @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="mb-3">
              <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
              <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary">
              <i class="fa fa-save me-1"></i> Simpan
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
@endsection
