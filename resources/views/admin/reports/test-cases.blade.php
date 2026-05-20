@extends('admin.reports.layout')

@section('title', 'Test Cases - ' . $project->name)

@section('content')
  {{-- Header --}}
  <div class="report-header">
    <h1>Daftar Test Cases</h1>
    <div class="subtitle">{{ $project->name }}</div>
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
        <div class="meta-label">Total Suites</div>
        <div class="meta-value">{{ $project->testSuites->count() }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Total Test Cases</div>
        <div class="meta-value">{{ $project->testSuites->sum(fn($s) => $s->testCases->count()) + $project->testCases->count() }}</div>
      </div>
    </div>
  </div>

  {{-- Per Suite --}}
  @foreach ($project->testSuites as $suite)
    <div class="section-title">{{ $suite->name }}</div>
    @if ($suite->description)
      <p style="font-size: 10px; color: #6c757d; margin-bottom: 8px;">{{ $suite->description }}</p>
    @endif

    @if ($suite->testCases->isEmpty())
      <p style="font-size: 10px; color: #6c757d; font-style: italic;">Belum ada test case di suite ini.</p>
    @else
      <table>
        <thead>
          <tr>
            <th style="width: 30px;">#</th>
            <th style="width: 60px;">Method</th>
            <th>Endpoint</th>
            <th>Title</th>
            <th style="width: 50px;">Expected</th>
            <th>Expected Contains</th>
            <th>Body Params</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($suite->testCases as $case)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td><span class="badge badge-{{ strtolower($case->method) }}">{{ $case->method }}</span></td>
              <td><code>{{ $case->endpoint }}</code></td>
              <td><strong>{{ $case->title }}</strong>
                @if ($case->description)
                  <br><small style="color: #6c757d;">{{ $case->description }}</small>
                @endif
              </td>
              <td style="text-align: center;">{{ $case->expected_status }}</td>
              <td>{{ $case->expected_contains ?? '—' }}</td>
              <td style="font-size: 9px;">{{ $case->body_params ? json_encode($case->body_params) : '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  @endforeach

  {{-- Tanpa Suite --}}
  @if ($project->testCases->isNotEmpty())
    <div class="section-title">Tanpa Suite</div>
    <table>
      <thead>
        <tr>
          <th style="width: 30px;">#</th>
          <th style="width: 60px;">Method</th>
          <th>Endpoint</th>
          <th>Title</th>
          <th style="width: 50px;">Expected</th>
          <th>Expected Contains</th>
          <th>Body Params</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($project->testCases as $case)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><span class="badge badge-{{ strtolower($case->method) }}">{{ $case->method }}</span></td>
            <td><code>{{ $case->endpoint }}</code></td>
            <td><strong>{{ $case->title }}</strong>
              @if ($case->description)
                <br><small style="color: #6c757d;">{{ $case->description }}</small>
              @endif
            </td>
            <td style="text-align: center;">{{ $case->expected_status }}</td>
            <td>{{ $case->expected_contains ?? '—' }}</td>
            <td style="font-size: 9px;">{{ $case->body_params ? json_encode($case->body_params) : '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  @endif
@endsection
