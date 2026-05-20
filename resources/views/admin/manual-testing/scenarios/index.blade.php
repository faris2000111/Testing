@extends('admin.template.main')

@section('title', 'Manual Testing - ' . $project->name)
@section('page_title', 'Manual Testing')

@section('content')
  <x-admin.page-header
    icon="fa-clipboard-check"
    icon-gradient="success"
    title="Manual Test Scenarios"
    :description="'Skenario manual testing untuk project: ' . $project->name"
  >
    <div class="d-flex gap-2">
      <a href="{{ route('admin.manual-testing.executions.index', $project) }}" class="btn btn-outline-info btn-sm mb-0">
        <i class="fa fa-clock-rotate-left me-1"></i> Riwayat Execution
      </a>
      <a href="{{ route('admin.manual-testing.executions.create', $project) }}" class="btn btn-success btn-sm mb-0">
        <i class="fa fa-play me-1"></i> Mulai Testing
      </a>
      <a href="{{ route('admin.manual-testing.scenarios.create', $project) }}" class="btn btn-primary btn-sm mb-0">
        <i class="fa fa-plus me-1"></i> Tambah Scenario
      </a>
    </div>
  </x-admin.page-header>

  {{-- Quick Stats --}}
  <div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-info">
            <i class="fa fa-globe"></i>
          </span>
          <div>
            <span class="stat-card__label">Project</span>
            <strong class="stat-card__value text-xs">{{ $project->name }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-primary">
            <i class="fa fa-clipboard-list"></i>
          </span>
          <div>
            <span class="stat-card__label">Total Scenarios</span>
            <strong class="stat-card__value">{{ $scenarios->count() }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-success">
            <i class="fa fa-list-ol"></i>
          </span>
          <div>
            <span class="stat-card__label">Total Steps</span>
            <strong class="stat-card__value">{{ $scenarios->sum('steps_count') }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-warning">
            <i class="fa fa-link"></i>
          </span>
          <div>
            <span class="stat-card__label">Base URL</span>
            <strong class="stat-card__value text-xs">{{ $project->base_url }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($scenarios->isEmpty())
        <x-admin.empty-state
          icon="fa-clipboard-check"
          title="Belum ada scenario"
          description="Tambahkan test scenario untuk mulai manual testing."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0 js-datatable">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Scenario</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Module</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Priority</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Steps</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($scenarios as $scenario)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{ route('admin.manual-testing.scenarios.show', [$project, $scenario]) }}" class="font-weight-bold text-decoration-none">
                      {{ $scenario->title }}
                    </a>
                    @if ($scenario->description)
                      <br><small class="text-muted">{{ Str::limit($scenario->description, 60) }}</small>
                    @endif
                  </td>
                  <td>
                    @if ($scenario->module)
                      <span class="badge bg-gradient-dark">{{ $scenario->module }}</span>
                    @else
                      <span class="text-muted">—</span>
                    @endif
                  </td>
                  <td>
                    <span class="badge bg-gradient-{{ $scenario->getPriorityBadgeColor() }}">
                      {{ $scenario->getPriorityLabel() }}
                    </span>
                  </td>
                  <td><span class="badge bg-gradient-secondary">{{ $scenario->steps_count }} steps</span></td>
                  <td>
                    @if ($scenario->is_active)
                      <span class="badge bg-gradient-success">Active</span>
                    @else
                      <span class="badge bg-gradient-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.manual-testing.scenarios.show', [$project, $scenario]) }}" class="btn btn-sm btn-outline-info me-1" title="Detail">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.manual-testing.scenarios.edit', [$project, $scenario]) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                      <i class="fa fa-pen"></i>
                    </a>
                    <form action="{{ route('admin.manual-testing.scenarios.destroy', [$project, $scenario]) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal" data-title="Hapus scenario {{ $scenario->title }}?">
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
