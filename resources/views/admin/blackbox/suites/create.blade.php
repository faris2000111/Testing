@extends('admin.template.main')

@section('title', 'Tambah Test Suite')
@section('page_title', 'Tambah Test Suite')

@section('content')
  <x-admin.page-header
    icon="fa-folder-plus"
    icon-gradient="primary"
    title="Tambah Test Suite"
    :description="'Buat grup test case untuk project: ' . $project->name"
  >
    <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.blackbox.projects.suites.store', $project) }}" method="POST">
        @csrf
        @include('admin.blackbox.suites.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
