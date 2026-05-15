@extends('admin.template.main')

@section('title', 'Profil Saya')
@section('page_title', 'Profil Saya')

@section('content')
  <x-admin.page-header
    icon="fa-user"
    icon-gradient="primary"
    title="Profil Saya"
    description="Ubah nama, username, email, dan foto profil."
  />

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row">
          <div class="col-md-3 text-center mb-4">
            <div class="mb-3">
              @if ($user->avatar)
                <img src="{{ Storage::disk('public')->url($user->avatar) }}" alt="Avatar"
                     class="rounded-circle shadow" style="width: 120px; height: 120px; object-fit: cover;">
              @else
                <div class="rounded-circle bg-gradient-primary d-inline-flex align-items-center justify-content-center shadow"
                     style="width: 120px; height: 120px; font-size: 2.5rem; color: #fff;">
                  {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
              @endif
            </div>
            <label class="btn btn-sm btn-outline-primary">
              <i class="fa fa-camera me-1"></i> Ganti Foto
              <input type="file" name="avatar" accept="image/*" class="d-none">
            </label>
            @error('avatar')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
          </div>

          <div class="col-md-9">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required>
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Username <span class="text-danger">*</span></label>
                <input type="text" name="username" class="form-control @error('username') is-invalid @enderror"
                       value="{{ old('username', $user->username) }}" required pattern="[a-z0-9\-_]+">
                @error('username')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" placeholder="Opsional">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
              </div>

              <div class="col-md-6 mb-3">
                <label class="form-label">Role</label>
                <input type="text" class="form-control" value="{{ $user->role->label ?? 'No Role' }}" disabled>
              </div>
            </div>
          </div>
        </div>

        <div class="d-flex justify-content-end mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Profil
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
