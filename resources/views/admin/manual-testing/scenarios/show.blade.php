@extends('admin.template.main')

@section('title', $scenario->title)
@section('page_title', $scenario->title)

@section('content')
  <x-admin.page-header
    icon="fa-clipboard-check"
    icon-gradient="success"
    :title="$scenario->title"
    :description="$scenario->description ?? 'Detail test scenario'"
  >
    <div class="d-flex gap-2">
      <a href="{{ route('admin.manual-testing.scenarios.edit', [$project, $scenario]) }}" class="btn btn-primary btn-sm mb-0">
        <i class="fa fa-pen me-1"></i> Edit
      </a>
      <a href="{{ route('admin.manual-testing.scenarios.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa fa-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </x-admin.page-header>

  {{-- Scenario Info --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-3 mb-3">
          <span class="text-xs text-muted d-block">Module</span>
          <strong>{{ $scenario->module ?? '—' }}</strong>
        </div>
        <div class="col-md-3 mb-3">
          <span class="text-xs text-muted d-block">Priority</span>
          <span class="badge bg-gradient-{{ $scenario->getPriorityBadgeColor() }}">{{ $scenario->getPriorityLabel() }}</span>
        </div>
        <div class="col-md-3 mb-3">
          <span class="text-xs text-muted d-block">Status</span>
          @if ($scenario->is_active)
            <span class="badge bg-gradient-success">Active</span>
          @else
            <span class="badge bg-gradient-secondary">Inactive</span>
          @endif
        </div>
        <div class="col-md-3 mb-3">
          <span class="text-xs text-muted d-block">Total Steps</span>
          <strong>{{ $scenario->steps->count() }}</strong>
        </div>
        @if ($scenario->precondition)
          <div class="col-12">
            <span class="text-xs text-muted d-block">Precondition</span>
            <p class="mb-0">{{ $scenario->precondition }}</p>
          </div>
        @endif
      </div>
    </div>
  </div>

  {{-- Steps --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header pb-0">
      <h6 class="mb-0"><i class="fa fa-list-ol me-1 text-primary"></i> Langkah-langkah Testing</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-items-center mb-0">
          <thead>
            <tr>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Step</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Expected Result</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Test Data</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($scenario->steps as $step)
              <tr>
                <td class="ps-3"><span class="badge bg-gradient-dark">{{ $step->step_number }}</span></td>
                <td class="text-sm" style="white-space: pre-wrap; max-width: 300px;">{{ $step->action }}</td>
                <td class="text-sm" style="white-space: pre-wrap; max-width: 300px;">{{ $step->expected_result }}</td>
                <td class="text-sm">{{ $step->test_data ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
