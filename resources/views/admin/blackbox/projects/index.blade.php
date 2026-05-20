@extends('admin.template.main')

@section('title', 'Blackbox Testing')
@section('page_title', 'Blackbox Testing')

@section('content')
  <x-admin.page-header
    icon="fa-vial"
    icon-gradient="info"
    title="Blackbox Testing"
    description="Kelola project dan jalankan blackbox testing pada website target."
  >
    <a href="{{ route('admin.blackbox.projects.create') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah Project
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($projects->isEmpty())
        <x-admin.empty-state
          icon="fa-vial"
          title="Belum ada project"
          description="Tambahkan project website yang ingin kamu testing."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0 js-datatable">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Base URL</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Test Cases</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Run</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($projects as $project)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="font-weight-bold text-decoration-none">
                      {{ $project->name }}
                    </a>
                  </td>
                  <td><code class="text-xs">{{ $project->base_url }}</code></td>
                  <td>
                    <span class="badge bg-gradient-secondary">{{ $project->test_cases_count }} cases</span>
                  </td>
                  <td>
                    @if ($project->latestRun)
                      <span class="text-xs">{{ $project->latestRun->created_at->diffForHumans() }}</span>
                    @else
                      <span class="text-xs text-muted">Belum pernah</span>
                    @endif
                  </td>
                  <td>
                    @if ($project->is_active)
                      <span class="badge bg-gradient-success">Active</span>
                    @else
                      <span class="badge bg-gradient-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="btn btn-sm btn-outline-info me-1" title="Detail">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.blackbox.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                      <i class="fa fa-pen"></i>
                    </a>
                    <form action="{{ route('admin.blackbox.projects.destroy', $project) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal" data-title="Hapus project {{ $project->name }}?">
                        <i class="fa fa-trash"></i>
                      </button>
                    </form>
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
