@extends('admin.reports.layout')

@section('title', 'Manual Test Report - ' . $project->name . ' - Execution #' . $execution->id)

@section('content')
  {{-- Header --}}
  <div class="report-header">
    <h1>Manual Test Report</h1>
    <div class="subtitle">{{ $project->name }} — Execution #{{ $execution->id }}</div>
    <div class="report-meta">
      <div class="meta-item">
        <div class="meta-label">Project</div>
        <div class="meta-value">{{ $project->name }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Base URL</div>
        <div class="meta-value">{{ $project->base_url }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Tester</div>
        <div class="meta-value">{{ $execution->user?->name ?? '—' }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Tanggal</div>
        <div class="meta-value">{{ $execution->created_at->format('d M Y H:i:s') }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Environment</div>
        <div class="meta-value">{{ $execution->environment ?? '—' }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Status</div>
        <div class="meta-value">{{ $execution->getStatusLabel() }}</div>
      </div>
    </div>
  </div>

  {{-- Summary --}}
  <div class="summary-grid">
    <div class="summary-box">
      <div class="value">{{ $execution->total_scenarios }}</div>
      <div class="label">Total Scenarios</div>
    </div>
    <div class="summary-box passed">
      <div class="value">{{ $execution->passed }}</div>
      <div class="label">Passed</div>
    </div>
    <div class="summary-box failed">
      <div class="value">{{ $execution->failed }}</div>
      <div class="label">Failed</div>
    </div>
    <div class="summary-box skipped">
      <div class="value">{{ $execution->skipped }}</div>
      <div class="label">Skipped</div>
    </div>
    <div class="summary-box">
      <div class="value">{{ $execution->getPassRate() }}%</div>
      <div class="label">Pass Rate</div>
    </div>
  </div>

  {{-- Progress Bar --}}
  @if ($execution->total_scenarios > 0)
    <div class="progress-bar-container">
      <div class="bar-passed" style="width: {{ ($execution->passed / $execution->total_scenarios) * 100 }}%"></div>
      <div class="bar-failed" style="width: {{ ($execution->failed / $execution->total_scenarios) * 100 }}%"></div>
      <div class="bar-skipped" style="width: {{ ($execution->skipped / $execution->total_scenarios) * 100 }}%"></div>
    </div>
  @endif

  {{-- Notes --}}
  @if ($execution->notes)
    <div class="scenario-notes" style="margin-bottom: 15px; border-radius: 6px;">
      <strong>Catatan Execution:</strong> {{ $execution->notes }}
    </div>
  @endif

  {{-- Scenario Results --}}
  <div class="section-title">Detail Hasil Per Scenario</div>

  @foreach ($execution->scenarioResults as $scenarioResult)
    <div class="scenario-block">
      <div class="scenario-header">
        <span>
          @if ($scenarioResult->scenario?->module)
            [{{ $scenarioResult->scenario->module }}]
          @endif
          {{ $scenarioResult->scenario?->title ?? 'Scenario Deleted' }}
          @if ($scenarioResult->scenario)
            <span class="badge badge-{{ $scenarioResult->scenario->priority }}">{{ $scenarioResult->scenario->getPriorityLabel() }}</span>
          @endif
        </span>
        <span class="badge badge-{{ $scenarioResult->status }}">{{ strtoupper($scenarioResult->status) }}</span>
      </div>

      @if ($scenarioResult->actual_result || $scenarioResult->notes)
        <div class="scenario-notes">
          @if ($scenarioResult->actual_result)
            <strong>Actual Result:</strong> {{ $scenarioResult->actual_result }}<br>
          @endif
          @if ($scenarioResult->notes)
            <strong>Notes:</strong> {{ $scenarioResult->notes }}
          @endif
        </div>
      @endif

      @if ($scenarioResult->scenario?->precondition)
        <div class="scenario-notes" style="background: #e8f4fd;">
          <strong>Precondition:</strong> {{ $scenarioResult->scenario->precondition }}
        </div>
      @endif

      <div class="scenario-body">
        <table style="margin-bottom: 0;">
          <thead>
            <tr>
              <th style="width: 40px;">Step</th>
              <th>Aksi</th>
              <th>Expected Result</th>
              <th>Test Data</th>
              <th style="width: 60px;">Status</th>
              <th>Actual Result</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($scenarioResult->stepResults as $stepResult)
              <tr class="row-{{ $stepResult->status }}">
                <td style="text-align: center; font-weight: 700;">{{ $stepResult->step?->step_number ?? '?' }}</td>
                <td>{{ $stepResult->step?->action ?? '—' }}</td>
                <td>{{ $stepResult->step?->expected_result ?? '—' }}</td>
                <td>{{ $stepResult->step?->test_data ?? '—' }}</td>
                <td><span class="badge badge-{{ $stepResult->status }}">{{ strtoupper($stepResult->status) }}</span></td>
                <td>{{ $stepResult->actual_result ?? '—' }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endforeach

  {{-- Summary Table --}}
  <div class="section-title">Ringkasan Scenario</div>
  <table>
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th>Scenario</th>
        <th style="width: 80px;">Module</th>
        <th style="width: 60px;">Priority</th>
        <th style="width: 60px;">Status</th>
        <th>Catatan</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($execution->scenarioResults as $scenarioResult)
        <tr class="row-{{ $scenarioResult->status }}">
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $scenarioResult->scenario?->title ?? '—' }}</strong></td>
          <td>{{ $scenarioResult->scenario?->module ?? '—' }}</td>
          <td>
            @if ($scenarioResult->scenario)
              <span class="badge badge-{{ $scenarioResult->scenario->priority }}">{{ $scenarioResult->scenario->getPriorityLabel() }}</span>
            @endif
          </td>
          <td><span class="badge badge-{{ $scenarioResult->status }}">{{ strtoupper($scenarioResult->status) }}</span></td>
          <td>{{ $scenarioResult->actual_result ?? '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
@endsection
