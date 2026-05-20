@extends('admin.template.main')

@section('title', 'Manual Testing')
@section('page_title', 'Manual Testing')

@section('content')
  <x-admin.page-header
    icon="fa-clipboard-check"
    icon-gradient="success"
    title="Manual Testing"
    description="Pilih project untuk mengelola skenario dan menjalankan manual testing."
  />

  @if ($projects->isEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <x-admin.empty-state
          icon="fa-clipboard-check"
          title="Belum ada project"
          description="Tambahkan project terlebih dahulu di menu Blackbox Testing."
        />
      </div>
    </div>
  @else
    <div class="row g-3">
      @foreach ($projects as $project)
        <div class="col-xl-4 col-md-6">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
              <div class="d-flex align-items-center gap-3 mb-3">
                <span class="stat-card__icon bg-gradient-success">
                  <i class="fa fa-clipboard-check"></i>
                </span>
                <div>
                  <h6 class="mb-0">{{ $project->name }}</h6>
                  <small class="text-muted">{{ $project->base_url }}</small>
                </div>
              </div>

              @if ($project->description)
                <p class="text-sm text-muted mb-3">{{ Str::limit($project->description, 100) }}</p>
              @endif

              <div class="d-flex gap-2 mb-3">
                <span class="badge bg-gradient-primary">{{ $project->manual_scenarios_count }} scenarios</span>
                <span class="badge bg-gradient-info">{{ $project->manual_executions_count }} executions</span>
              </div>

              <div class="d-flex gap-2">
                <a href="{{ route('admin.manual-testing.scenarios.index', $project) }}" class="btn btn-sm btn-outline-primary">
                  <i class="fa fa-list me-1"></i> Scenarios
                </a>
                <a href="{{ route('admin.manual-testing.executions.create', $project) }}" class="btn btn-sm btn-success">
                  <i class="fa fa-play me-1"></i> Mulai Testing
                </a>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @endif
@endsection
