@extends('admin.template.main')

@section('title', 'Edit Test Suite')
@section('page_title', 'Edit Test Suite')

@section('content')
  <x-admin.page-header
    icon="fa-pen"
    icon-gradient="primary"
    title="Edit Test Suite"
    :description="'Ubah suite: ' . $suite->name"
  >
    <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route('admin.blackbox.projects.suites.update', [$project, $suite]) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.blackbox.suites.form')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
