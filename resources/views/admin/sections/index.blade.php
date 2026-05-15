@extends('admin.template.main')

@section('title', 'Section Manager')
@section('page_title', 'Section Manager')

@section('content')
  <x-admin.page-header
    icon="fa-layer-group"
    icon-gradient="success"
    title="Section Manager"
    description="Kelola section (grup) untuk menu sidebar."
  >
    <a href="{{ route('admin.sections.create') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah Section
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($sections->isEmpty())
        <x-admin.empty-state
          icon="fa-layer-group"
          title="Belum ada section"
          description="Tambahkan section untuk mengelompokkan menu di sidebar."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Urutan</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama Section</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah Menu</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($sections as $section)
                <tr>
                  <td class="ps-4">{{ $section->order }}</td>
                  <td><span class="font-weight-bold">{{ $section->name }}</span></td>
                  <td>{{ $section->menus_count }} menu</td>
                  <td>
                    <a href="{{ route('admin.sections.edit', $section) }}" class="btn btn-sm btn-outline-primary me-1">
                      <i class="fa fa-pen"></i>
                    </a>
                    @if ($section->menus_count === 0)
                      <form action="{{ route('admin.sections.destroy', $section) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    @endif
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  </div>
@endsection
