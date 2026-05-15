@extends('admin.template.main')

@section('title', 'Edit User')
@section('page_title', 'Edit User')

@section('content')
  <x-admin.page-header
    icon="fa-pen-to-square"
    icon-gradient="warning"
    title="Edit User: {{ $user->name }}"
    description="Ubah data user."
  >
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.users.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
