@extends('admin.template.main')

@section('title', 'Tambah Menu')
@section('page_title', 'Tambah Menu')

@section('content')
  <x-admin.page-header
    icon="fa-plus"
    icon-gradient="primary"
    title="Tambah Menu Baru"
    description="Buat menu baru untuk sidebar admin."
  >
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.menus.store') }}" method="POST">
        @csrf
        @include('admin.menu.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
