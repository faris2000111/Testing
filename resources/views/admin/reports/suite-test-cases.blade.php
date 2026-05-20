@extends('admin.reports.layout')

@section('title', $suite->name . ' - ' . $project->name)

@section('content')
  {{-- Header --}}
  <div class="report-header">
    <h1>{{ $suite->name }}</h1>
    <div class="subtitle">{{ $project->name }} — Test Suite</div>
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
        <div class="meta-label">Suite</div>
        <div class="meta-value">{{ $suite->name }}</div>
      </div>
      <div class="meta-item">
        <div class="meta-label">Total Test Cases</div>
        <div class="meta-value">{{ $suite->testCases->count() }}</div>
      </div>
    </div>
  </div>

  @if ($suite->description)
    <p style="font-size: 11px; color: #495057; margin-bottom: 15px;">{{ $suite->description }}</p>
  @endif

  {{-- Test Cases Table --}}
  <div class="section-title">Daftar Test Cases</div>

  @if ($suite->testCases->isEmpty())
    <p style="color: #6c757d; font-style: italic;">Belum ada test case di suite ini.</p>
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
          <th>Expected Not Contains</th>
          <th>Body Params</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($suite->testCases as $case)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td><span class="badge badge-{{ strtolower($case->method) }}">{{ $case->method }}</span></td>
            <td><code>{{ $case->endpoint }}</code></td>
            <td>
              <strong>{{ $case->title }}</strong>
              @if ($case->description)
                <br><small style="color: #6c757d;">{{ $case->description }}</small>
              @endif
            </td>
            <td style="text-align: center;">{{ $case->expected_status }}</td>
            <td>{{ $case->expected_contains ?? '—' }}</td>
            <td>{{ $case->expected_not_contains ?? '—' }}</td>
            <td style="font-size: 9px;">{{ $case->body_params ? json_encode($case->body_params) : '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>

    {{-- Detail per test case --}}
    <div class="section-title">Detail Test Cases</div>
    @foreach ($suite->testCases as $case)
      <div class="scenario-block">
        <div class="scenario-header">
          <span>
            <span class="badge badge-{{ strtolower($case->method) }}">{{ $case->method }}</span>
            {{ $case->title }}
          </span>
          <span>Expected: {{ $case->expected_status }}</span>
        </div>
        <div class="scenario-notes">
          <strong>Endpoint:</strong> {{ $case->endpoint }}<br>
          @if ($case->description)
            <strong>Deskripsi:</strong> {{ $case->description }}<br>
          @endif
          @if ($case->headers)
            <strong>Headers:</strong> <code>{{ json_encode($case->headers) }}</code><br>
          @endif
          @if ($case->body_params)
            <strong>Body Params:</strong> <code>{{ json_encode($case->body_params) }}</code><br>
          @endif
          @if ($case->expected_contains)
            <strong>Response Harus Mengandung:</strong> {{ $case->expected_contains }}<br>
          @endif
          @if ($case->expected_not_contains)
            <strong>Response Tidak Boleh Mengandung:</strong> {{ $case->expected_not_contains }}<br>
          @endif
        </div>
      </div>
    @endforeach
  @endif
@endsection
