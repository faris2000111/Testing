@extends('admin.template.main')

@section('title', 'Edit Menu')
@section('page_title', 'Edit Menu')

@section('content')
  <x-admin.page-header
    icon="fa-pen-to-square"
    icon-gradient="warning"
    title="Edit Menu: {{ $menu->label }}"
    description="Ubah konfigurasi menu sidebar."
  >
    <a href="{{ route('admin.menus.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.menus.update', $menu) }}" method="POST">
        @csrf
        @method('PUT')
        @include('admin.menu.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
