@extends('admin.template.main')

@section('title', 'Tambah Project')
@section('page_title', 'Tambah Project')

@section('content')
  <x-admin.page-header
    icon="fa-plus"
    icon-gradient="primary"
    title="Tambah Project Baru"
    description="Tambahkan website target untuk blackbox testing."
  >
    <a href="{{ route('admin.blackbox.projects.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.blackbox.projects.store') }}" method="POST">
        @csrf
        @include('admin.blackbox.projects.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
