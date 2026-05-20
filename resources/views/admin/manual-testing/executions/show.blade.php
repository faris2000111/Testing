@extends('admin.template.main')

@section('title', 'Hasil Execution')
@section('page_title', 'Hasil Execution #' . $execution->id)

@section('content')
  <x-admin.page-header
    icon="fa-chart-bar"
    icon-gradient="info"
    :title="'Manual Test Report #' . $execution->id"
    :description="'Dijalankan oleh ' . ($execution->user?->name ?? '—') . ' pada ' . $execution->created_at->format('d M Y H:i')"
  >
    <div class="d-flex gap-2">
      <a href="{{ route('admin.reports.manual-execution', [$project, $execution]) }}" target="_blank" class="btn btn-success btn-sm mb-0">
        <i class="fa fa-file-pdf me-1"></i> Print PDF
      </a>
      <a href="{{ route('admin.manual-testing.executions.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa fa-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </x-admin.page-header>

  {{-- Summary Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-xl-2 col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <strong class="d-block fs-4">{{ $execution->total_scenarios }}</strong>
          <small class="text-muted">Total</small>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <strong class="d-block fs-4 text-success">{{ $execution->passed }}</strong>
          <small class="text-muted">Passed</small>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <strong class="d-block fs-4 text-danger">{{ $execution->failed }}</strong>
          <small class="text-muted">Failed</small>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <strong class="d-block fs-4 text-muted">{{ $execution->skipped }}</strong>
          <small class="text-muted">Skipped</small>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <strong class="d-block fs-4">{{ $execution->getPassRate() }}%</strong>
          <small class="text-muted">Pass Rate</small>
        </div>
      </div>
    </div>
    <div class="col-xl-2 col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <strong class="d-block fs-6">{{ $execution->environment ?? '—' }}</strong>
          <small class="text-muted">Environment</small>
        </div>
      </div>
    </div>
  </div>

  {{-- Pass Rate Bar --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-sm font-weight-bold">Pass Rate</span>
        <span class="text-sm font-weight-bold">{{ $execution->getPassRate() }}%</span>
      </div>
      <div class="progress" style="height: 10px;">
        @if ($execution->total_scenarios > 0)
          <div class="progress-bar bg-success" style="width: {{ ($execution->passed / $execution->total_scenarios) * 100 }}%"></div>
          <div class="progress-bar bg-danger" style="width: {{ ($execution->failed / $execution->total_scenarios) * 100 }}%"></div>
          <div class="progress-bar bg-secondary" style="width: {{ ($execution->skipped / $execution->total_scenarios) * 100 }}%"></div>
        @endif
      </div>
    </div>
  </div>

  {{-- Scenario Results --}}
  @foreach ($execution->scenarioResults as $scenarioResult)
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header p-3 d-flex justify-content-between align-items-center">
        <div>
          <h6 class="mb-0">
            <span class="badge bg-gradient-{{ $scenarioResult->getStatusBadge() }} me-1">{{ $scenarioResult->getStatusLabel() }}</span>
            {{ $scenarioResult->scenario?->title ?? 'Scenario Deleted' }}
          </h6>
          @if ($scenarioResult->scenario?->module)
            <small class="text-muted">Module: {{ $scenarioResult->scenario->module }}</small>
          @endif
        </div>
      </div>
      <div class="card-body pt-0">
        @if ($scenarioResult->actual_result)
          <div class="alert alert-light py-2 px-3 mb-3">
            <strong class="text-xs">Actual Result:</strong> {{ $scenarioResult->actual_result }}
          </div>
        @endif
        @if ($scenarioResult->notes)
          <div class="alert alert-light py-2 px-3 mb-3">
            <strong class="text-xs">Notes:</strong> {{ $scenarioResult->notes }}
          </div>
        @endif

        <div class="table-responsive">
          <table class="table table-sm align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-xs">Step</th>
                <th class="text-xs">Aksi</th>
                <th class="text-xs">Expected</th>
                <th class="text-xs">Status</th>
                <th class="text-xs">Actual Result</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($scenarioResult->stepResults as $stepResult)
                <tr class="{{ $stepResult->status === 'passed' ? 'table-success' : ($stepResult->status === 'failed' ? 'table-danger' : ($stepResult->status === 'blocked' ? 'table-warning' : '')) }}">
                  <td><span class="badge bg-gradient-dark">{{ $stepResult->step?->step_number ?? '?' }}</span></td>
                  <td class="text-sm" style="white-space: pre-wrap; max-width: 200px;">{{ $stepResult->step?->action ?? '—' }}</td>
                  <td class="text-sm" style="white-space: pre-wrap; max-width: 200px;">{{ $stepResult->step?->expected_result ?? '—' }}</td>
                  <td>
                    <span class="badge bg-gradient-{{ $stepResult->getStatusBadge() }}">{{ strtoupper($stepResult->status) }}</span>
                  </td>
                  <td class="text-sm">{{ $stepResult->actual_result ?? '—' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endforeach
@endsection
