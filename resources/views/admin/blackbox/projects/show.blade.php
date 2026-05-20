@extends('admin.template.main')

@section('title', $project->name . ' - Blackbox Testing')
@section('page_title', $project->name)

@section('content')
  <x-admin.page-header
    icon="fa-vial"
    icon-gradient="info"
    :title="$project->name"
    :description="$project->description ?? 'Blackbox testing untuk ' . $project->base_url"
  >
    <div class="d-flex gap-2">
      <button type="button" class="btn btn-success btn-sm mb-0" id="btnRunTests" onclick="runTests()">
        <i class="fa fa-play me-1"></i> Jalankan Semua Test
      </button>
      <a href="{{ route('admin.blackbox.projects.cases.create', $project) }}" class="btn btn-primary btn-sm mb-0">
        <i class="fa fa-plus me-1"></i> Tambah Test Case
      </a>
      <a href="{{ route('admin.blackbox.projects.index') }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa fa-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </x-admin.page-header>

  {{-- AI Generator --}}
  <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #8b5cf6 !important;">
    <div class="card-body py-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="fa fa-wand-magic-sparkles" style="color: #8b5cf6;"></i>
        <strong class="text-sm">AI Generate</strong>
        <small class="text-muted">— Deskripsikan fitur/halaman, test cases langsung tersimpan ke project.</small>
      </div>
      <div class="d-flex gap-2">
        <textarea id="aiBlackboxPrompt" class="form-control form-control-sm" rows="2"
          placeholder="Contoh: Generate test cases untuk halaman login, register, forgot password, dan halaman profil" style="flex: 1;"></textarea>
        <button type="button" class="btn btn-sm align-self-end" style="background: #8b5cf6; color: white; white-space: nowrap;" onclick="generateBlackboxCases()" id="btnAiBlackbox">
          <i class="fa fa-wand-magic-sparkles me-1"></i> Generate & Simpan
        </button>
      </div>
    </div>
  </div>

  {{-- Project Info --}}
  <div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-info">
            <i class="fa fa-globe"></i>
          </span>
          <div>
            <span class="stat-card__label">Base URL</span>
            <strong class="stat-card__value text-xs">{{ $project->base_url }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-primary">
            <i class="fa fa-list-check"></i>
          </span>
          <div>
            <span class="stat-card__label">Total Test Cases</span>
            <strong class="stat-card__value">{{ $project->testCases->count() }}</strong>
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
            <span class="stat-card__label">Total Runs</span>
            <strong class="stat-card__value">{{ $project->testRuns->count() }}</strong>
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
            <span class="stat-card__label">Last Run</span>
            <strong class="stat-card__value text-xs">
              @if ($project->testRuns->first())
                {{ $project->testRuns->first()->created_at->diffForHumans() }}
              @else
                Belum pernah
              @endif
            </strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Test Results Panel (hidden by default, shown after running) --}}
  <div class="card border-0 shadow-sm mb-4 d-none" id="testResultsPanel">
    <div class="card-header pb-0">
      <h6 class="mb-0"><i class="fa fa-chart-bar me-1 text-info"></i> Hasil Test Terakhir</h6>
    </div>
    <div class="card-body" id="testResultsBody">
      {{-- Filled by JS --}}
    </div>
  </div>

  {{-- Test Cases --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
      <div>
        <h6 class="mb-0"><i class="fa fa-list-check me-1 text-primary"></i> Test Cases</h6>
        <small class="text-muted">Daftar skenario testing yang akan dijalankan.</small>
      </div>
    </div>
    <div class="card-body">
      @if ($project->testCases->isEmpty())
        <x-admin.empty-state
          icon="fa-list-check"
          title="Belum ada test case"
          description="Tambahkan test case untuk mulai testing."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Method</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Endpoint</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Title</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Expected</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($project->testCases as $case)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    <span class="badge bg-gradient-{{ $case->getMethodBadgeColor() }}">{{ $case->method }}</span>
                  </td>
                  <td><code class="text-xs">{{ $case->endpoint }}</code></td>
                  <td><span class="text-sm font-weight-bold">{{ $case->title }}</span></td>
                  <td><code>{{ $case->expected_status }}</code></td>
                  <td>
                    @if ($case->is_active)
                      <span class="badge bg-gradient-success">Active</span>
                    @else
                      <span class="badge bg-gradient-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.blackbox.projects.cases.edit', [$project, $case]) }}" class="btn btn-sm btn-outline-primary me-1">
                      <i class="fa fa-pen"></i>
                    </a>
                    <form action="{{ route('admin.blackbox.projects.cases.destroy', [$project, $case]) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal" data-title="Hapus test case {{ $case->title }}?">
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

  {{-- Test Run History --}}
  @if ($project->testRuns->isNotEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-header pb-0">
        <h6 class="mb-0"><i class="fa fa-clock-rotate-left me-1 text-secondary"></i> Riwayat Test Run</h6>
        <small class="text-muted">10 test run terakhir.</small>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Waktu</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Passed</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Failed</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Duration</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($project->testRuns as $run)
                <tr>
                  <td class="ps-3 text-sm">{{ $run->created_at->format('d M Y H:i') }}</td>
                  <td class="text-sm">{{ $run->user?->name ?? '—' }}</td>
                  <td>
                    <span class="badge bg-gradient-{{ $run->getStatusBadge() }}">{{ $run->getStatusLabel() }}</span>
                  </td>
                  <td><span class="text-success font-weight-bold">{{ $run->passed }}</span></td>
                  <td><span class="text-danger font-weight-bold">{{ $run->failed }}</span></td>
                  <td class="text-sm">{{ number_format($run->duration_ms, 0) }} ms</td>
                  <td>
                    <a href="{{ route('admin.blackbox.projects.runs.show', [$project, $run]) }}" class="btn btn-sm btn-outline-info">
                      <i class="fa fa-eye"></i>
                    </a>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>
  @endif
@endsection

@push('scripts')
<script>
function runTests() {
  var btn = document.getElementById('btnRunTests');
  var panel = document.getElementById('testResultsPanel');
  var body = document.getElementById('testResultsBody');

  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Running...';

  panel.classList.remove('d-none');
  body.innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2 text-muted">Menjalankan test cases...</p></div>';

  fetch('{{ route("admin.blackbox.projects.run", $project) }}', {
    method: 'POST',
    headers: {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'X-Requested-With': 'XMLHttpRequest'
    }
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-play me-1"></i> Jalankan Semua Test';

    if (!data.success) {
      body.innerHTML = '<div class="alert alert-warning mb-0">' + data.message + '</div>';
      return;
    }

    var html = '';
    html += '<div class="row g-3 mb-3">';
    html += '<div class="col-md-3"><div class="p-3 rounded bg-light text-center"><strong class="d-block fs-4">' + data.total + '</strong><small class="text-muted">Total</small></div></div>';
    html += '<div class="col-md-3"><div class="p-3 rounded bg-light text-center"><strong class="d-block fs-4 text-success">' + data.passed + '</strong><small class="text-muted">Passed</small></div></div>';
    html += '<div class="col-md-3"><div class="p-3 rounded bg-light text-center"><strong class="d-block fs-4 text-danger">' + data.failed + '</strong><small class="text-muted">Failed</small></div></div>';
    html += '<div class="col-md-3"><div class="p-3 rounded bg-light text-center"><strong class="d-block fs-4">' + data.duration_ms + ' ms</strong><small class="text-muted">Duration</small></div></div>';
    html += '</div>';

    html += '<div class="table-responsive"><table class="table table-sm align-items-center mb-0">';
    html += '<thead><tr><th class="text-xs">Test Case</th><th class="text-xs">Status</th><th class="text-xs">HTTP Status</th><th class="text-xs">Time</th><th class="text-xs">Error</th></tr></thead><tbody>';

    data.results.forEach(function(r) {
      var badge = r.status === 'passed' ? 'success' : (r.status === 'error' ? 'warning' : 'danger');
      html += '<tr>';
      html += '<td class="text-sm">' + r.title + '</td>';
      html += '<td><span class="badge bg-gradient-' + badge + '">' + r.status.toUpperCase() + '</span></td>';
      html += '<td><code>' + (r.actual_status || '—') + '</code> / <code>' + r.expected_status + '</code></td>';
      html += '<td class="text-sm">' + (r.response_time_ms ? r.response_time_ms + ' ms' : '—') + '</td>';
      html += '<td class="text-sm text-danger">' + (r.error_message || '—') + '</td>';
      html += '</tr>';
    });

    html += '</tbody></table></div>';

    body.innerHTML = html;
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-play me-1"></i> Jalankan Semua Test';
    body.innerHTML = '<div class="alert alert-danger mb-0">Terjadi error: ' + err.message + '</div>';
  });
}

// ─── AI Blackbox Generator ───

function generateBlackboxCases() {
  var prompt = document.getElementById('aiBlackboxPrompt').value.trim();
  if (!prompt) {
    Swal.fire({ icon: 'warning', title: 'Prompt kosong', text: 'Masukkan deskripsi fitur/halaman yang ingin di-test.' });
    return;
  }

  var btn = document.getElementById('btnAiBlackbox');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Generating...';

  fetch('{{ route("admin.ai.generate-blackbox") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      prompt: prompt,
      project_name: '{{ $project->name }}',
      base_url: '{{ $project->base_url }}'
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (!data.success) {
      btn.disabled = false;
      btn.innerHTML = '<i class="fa fa-wand-magic-sparkles me-1"></i> Generate & Simpan';
      Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
      return;
    }

    // Langsung simpan semua test cases
    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Menyimpan...';
    saveGeneratedCases(data.data.test_cases, btn);
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-wand-magic-sparkles me-1"></i> Generate & Simpan';
    Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal: ' + err.message });
  });
}

function saveGeneratedCases(cases, btn) {
  var promises = cases.map(function(c) {
    var formData = new FormData();
    formData.append('_token', '{{ csrf_token() }}');
    formData.append('title', c.title || 'Untitled');
    formData.append('method', c.method || 'GET');
    formData.append('endpoint', c.endpoint || '/');
    formData.append('expected_status', c.expected_status || 200);
    formData.append('description', c.description || '');
    formData.append('expected_contains', c.expected_contains || '');
    formData.append('expected_not_contains', c.expected_not_contains || '');
    formData.append('is_active', '1');
    if (c.headers) formData.append('headers', JSON.stringify(c.headers));
    if (c.body_params) formData.append('body_params', JSON.stringify(c.body_params));

    return fetch('{{ route("admin.blackbox.projects.cases.store", $project) }}', {
      method: 'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body: formData
    });
  });

  Promise.all(promises).then(function() {
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: cases.length + ' test cases berhasil ditambahkan.',
      timer: 2000,
      showConfirmButton: false
    });
    setTimeout(function() { window.location.reload(); }, 2000);
  }).catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-wand-magic-sparkles me-1"></i> Generate & Simpan';
    Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menyimpan: ' + err.message });
  });
}

function escapeHtml(text) {
  if (!text) return '';
  var div = document.createElement('div');
  div.appendChild(document.createTextNode(text));
  return div.innerHTML;
}
</script>
@endpush
