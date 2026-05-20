@extends('admin.reports.layout')

@section('title', 'Blackbox Test Report - ' . $project->name . ' - Run #' . $run->id)

@section('content')
  {{-- Header --}}
  <div class="report-header">
    <h1>Blackbox Test Report</h1>
    <div class="subtitle">{{ $project->name }} — Test Run #{{ $run->id }}</div>
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
        <div class="meta-value">{{ $run->user?->name ?? '—' }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Tanggal</div>
        <div class="meta-value">{{ $run->created_at->format('d M Y H:i:s') }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Duration</div>
        <div class="meta-value">{{ number_format($run->duration_ms, 0) }} ms</div>
      </div>
    </div>
  </div>

  {{-- Summary --}}
  <div class="summary-grid">
    <div class="summary-box">
      <div class="value">{{ $run->total_cases }}</div>
      <div class="label">Total Cases</div>
    </div>
    <div class="summary-box passed">
      <div class="value">{{ $run->passed }}</div>
      <div class="label">Passed</div>
    </div>
    <div class="summary-box failed">
      <div class="value">{{ $run->failed }}</div>
      <div class="label">Failed</div>
    </div>
    <div class="summary-box">
      <div class="value">{{ $run->getPassRate() }}%</div>
      <div class="label">Pass Rate</div>
    </div>
  </div>

  {{-- Progress Bar --}}
  <div class="progress-bar-container">
    <div class="bar-passed" style="width: {{ $run->getPassRate() }}%"></div>
    @if ($run->failed > 0)
      <div class="bar-failed" style="width: {{ 100 - $run->getPassRate() }}%"></div>
    @endif
  </div>

  {{-- Results Table --}}
  <div class="section-title">Detail Hasil Test</div>
  <table>
    <thead>
      <tr>
        <th style="width: 30px;">#</th>
        <th>Test Case</th>
        <th style="width: 60px;">Method</th>
        <th>Endpoint</th>
        <th style="width: 60px;">Status</th>
        <th style="width: 90px;">HTTP Code</th>
        <th style="width: 60px;">Time</th>
        <th>Error</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($run->results as $result)
        <tr class="row-{{ $result->status }}">
          <td>{{ $loop->iteration }}</td>
          <td><strong>{{ $result->testCase?->title ?? '—' }}</strong></td>
          <td>
            @if ($result->testCase)
              <span class="badge badge-{{ strtolower($result->testCase->method) }}">{{ $result->testCase->method }}</span>
            @endif
          </td>
          <td><code>{{ $result->testCase?->endpoint ?? '—' }}</code></td>
          <td><span class="badge badge-{{ $result->status }}">{{ strtoupper($result->status) }}</span></td>
          <td>
            {{ $result->actual_status ?? '—' }} / {{ $result->testCase?->expected_status ?? '—' }}
          </td>
          <td>{{ $result->response_time_ms ? number_format($result->response_time_ms, 0) . ' ms' : '—' }}</td>
          <td style="color: #dc3545;">{{ $result->error_message ?? '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  {{-- Failed Cases Detail --}}
  @php $failedResults = $run->results->where('status', 'failed')->merge($run->results->where('status', 'error')); @endphp
  @if ($failedResults->isNotEmpty())
    <div class="section-title">Detail Kegagalan</div>
    @foreach ($failedResults as $result)
      <div class="scenario-block">
        <div class="scenario-header">
          <span>{{ $result->testCase?->title ?? '—' }} — {{ $result->testCase?->method }} {{ $result->testCase?->endpoint }}</span>
          <span class="badge badge-{{ $result->status }}">{{ strtoupper($result->status) }}</span>
        </div>
        <div class="scenario-notes">
          <strong>Error:</strong> {{ $result->error_message ?? 'Tidak ada pesan error.' }}<br>
          <strong>Expected Status:</strong> {{ $result->testCase?->expected_status ?? '—' }} |
          <strong>Actual Status:</strong> {{ $result->actual_status ?? '—' }} |
          <strong>Response Time:</strong> {{ $result->response_time_ms ? number_format($result->response_time_ms, 0) . ' ms' : '—' }}
        </div>
      </div>
    @endforeach
  @endif
@endsection
