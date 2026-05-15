@extends('admin.template.main')

@section('title', 'Edit Role')
@section('page_title', 'Edit Role')

@section('content')
  <x-admin.page-header
    icon="fa-pen-to-square"
    icon-gradient="warning"
    title="Edit Role: {{ $role->label }}"
    description="Ubah role dan hak akses menu."
  >
    <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.roles.update', $role) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.roles.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
