@extends('admin.template.main')

@section('title', 'Tambah User')
@section('page_title', 'Tambah User')

@section('content')
  <x-admin.page-header
    icon="fa-plus"
    icon-gradient="primary"
    title="Tambah User Baru"
    description="Buat user baru untuk admin panel."
  >
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf
        @include('admin.users.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
