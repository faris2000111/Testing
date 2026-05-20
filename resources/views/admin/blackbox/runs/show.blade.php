@extends('admin.template.main')

@section('title', 'Test Run Detail')
@section('page_title', 'Test Run #' . $run->id)

@section('content')
  <x-admin.page-header
    icon="fa-chart-bar"
    icon-gradient="info"
    :title="'Test Run #' . $run->id"
    :description="'Dijalankan oleh ' . ($run->user?->name ?? '—') . ' pada ' . $run->created_at->format('d M Y H:i:s')"
  >
    <div class="d-flex gap-2">
      <a href="{{ route('admin.reports.blackbox-run', [$project, $run]) }}" target="_blank" class="btn btn-success btn-sm mb-0">
        <i class="fa fa-file-pdf me-1"></i> Print PDF
      </a>
      <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Project
      </a>
    </div>
  </x-admin.page-header>

  {{-- Summary Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-info">
            <i class="fa fa-list-check"></i>
          </span>
          <div>
            <span class="stat-card__label">Total Cases</span>
            <strong class="stat-card__value">{{ $run->total_cases }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-success">
            <i class="fa fa-circle-check"></i>
          </span>
          <div>
            <span class="stat-card__label">Passed</span>
            <strong class="stat-card__value text-success">{{ $run->passed }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-danger">
            <i class="fa fa-circle-xmark"></i>
          </span>
          <div>
            <span class="stat-card__label">Failed</span>
            <strong class="stat-card__value text-danger">{{ $run->failed }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-warning">
            <i class="fa fa-clock"></i>
          </span>
          <div>
            <span class="stat-card__label">Duration</span>
            <strong class="stat-card__value">{{ number_format($run->duration_ms, 0) }} ms</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Pass Rate Bar --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-sm font-weight-bold">Pass Rate</span>
        <span class="text-sm font-weight-bold">{{ $run->getPassRate() }}%</span>
      </div>
      <div class="progress" style="height: 10px;">
        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $run->getPassRate() }}%"></div>
        @if ($run->failed > 0)
          <div class="progress-bar bg-danger" role="progressbar" style="width: {{ 100 - $run->getPassRate() }}%"></div>
        @endif
      </div>
    </div>
  </div>

  {{-- Detailed Results --}}
  <div class="card border-0 shadow-sm">
    <div class="card-header pb-0">
      <h6 class="mb-0"><i class="fa fa-list me-1 text-primary"></i> Detail Hasil</h6>
    </div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table align-items-center mb-0">
          <thead>
            <tr>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">#</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Test Case</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Method</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Endpoint</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">HTTP Code</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Time</th>
              <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Error</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($run->results as $result)
              <tr>
                <td class="ps-3">{{ $loop->iteration }}</td>
                <td class="text-sm font-weight-bold">{{ $result->getTitle() }}</td>
                <td>
                  @if ($result->getMethod())
                    <span class="badge bg-gradient-{{ $result->getMethodBadgeColor() }}">{{ $result->getMethod() }}</span>
                  @endif
                </td>
                <td><code class="text-xs">{{ $result->getEndpoint() ?? '—' }}</code></td>
                <td>
                  <span class="badge bg-gradient-{{ $result->getStatusBadge() }}">{{ strtoupper($result->status) }}</span>
                </td>
                <td>
                  <code>{{ $result->actual_status ?? '—' }}</code>
                  <span class="text-muted">/</span>
                  <code>{{ $result->getExpectedStatus() ?? '—' }}</code>
                </td>
                <td class="text-sm">{{ $result->response_time_ms ? number_format($result->response_time_ms, 0) . ' ms' : '—' }}</td>
                <td class="text-sm text-danger">{{ $result->error_message ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
@endsection
