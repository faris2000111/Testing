@extends('admin.template.main')

@section('title', 'Jalankan Manual Test')
@section('page_title', 'Jalankan Manual Test')

@section('content')
  <x-admin.page-header
    icon="fa-play-circle"
    icon-gradient="success"
    title="Manual Test Execution"
    :description="'Project: ' . $project->name . ($execution->environment ? ' | Environment: ' . $execution->environment : '')"
  >
    <div class="d-flex gap-2">
      <form action="{{ route('admin.manual-testing.executions.complete', [$project, $execution]) }}" method="POST" class="d-inline" id="completeForm">
        @csrf
        <button type="button" class="btn btn-primary btn-sm mb-0" onclick="confirmComplete()">
          <i class="fa fa-check-double me-1"></i> Selesaikan Testing
        </button>
      </form>
      <a href="{{ route('admin.manual-testing.executions.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa fa-arrow-left me-1"></i> Kembali
      </a>
    </div>
  </x-admin.page-header>

  {{-- Progress Overview --}}
  <div class="card border-0 shadow-sm mb-4">
    <div class="card-body p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="text-sm font-weight-bold">Progress</span>
        <span class="text-sm" id="progressText">0 / {{ $execution->total_scenarios }} scenarios</span>
      </div>
      <div class="progress" style="height: 8px;">
        <div class="progress-bar bg-success" id="progressBar" role="progressbar" style="width: 0%"></div>
      </div>
    </div>
  </div>

  {{-- Scenarios Accordion --}}
  <div class="accordion" id="scenariosAccordion">
    @foreach ($execution->scenarioResults as $scenarioResult)
      @php $scenario = $scenarioResult->scenario; @endphp
      <div class="card border-0 shadow-sm mb-3" id="scenario-card-{{ $scenarioResult->id }}">
        <div class="card-header p-3 d-flex justify-content-between align-items-center" style="cursor: pointer;"
          data-bs-toggle="collapse" data-bs-target="#collapse-{{ $scenarioResult->id }}">
          <div>
            <h6 class="mb-0">
              <span class="badge bg-gradient-{{ $scenario->getPriorityBadgeColor() }} me-1">{{ $scenario->getPriorityLabel() }}</span>
              {{ $scenario->title }}
            </h6>
            @if ($scenario->module)
              <small class="text-muted">Module: {{ $scenario->module }}</small>
            @endif
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-gradient-secondary" id="scenario-status-badge-{{ $scenarioResult->id }}">{{ strtoupper($scenarioResult->status) }}</span>
            <i class="fa fa-chevron-down"></i>
          </div>
        </div>

        <div class="collapse {{ $loop->first ? 'show' : '' }}" id="collapse-{{ $scenarioResult->id }}" data-bs-parent="#scenariosAccordion">
          <div class="card-body pt-0">
            {{-- Precondition --}}
            @if ($scenario->precondition)
              <div class="alert alert-info py-2 px-3 mb-3">
                <i class="fa fa-info-circle me-1"></i> <strong>Precondition:</strong> {{ $scenario->precondition }}
              </div>
            @endif

            {{-- Steps Table --}}
            <div class="table-responsive">
              <table class="table table-sm align-items-center mb-3">
                <thead>
                  <tr>
                    <th class="text-xs" style="width: 50px;">Step</th>
                    <th class="text-xs">Aksi</th>
                    <th class="text-xs">Expected Result</th>
                    <th class="text-xs">Test Data</th>
                    <th class="text-xs" style="width: 120px;">Status</th>
                    <th class="text-xs" style="width: 80px;">Detail</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($scenarioResult->stepResults as $stepResult)
                    @php $step = $stepResult->step; @endphp
                    <tr id="step-row-{{ $stepResult->id }}">
                      <td><span class="badge bg-gradient-dark">{{ $step->step_number }}</span></td>
                      <td class="text-sm" style="white-space: pre-wrap; max-width: 200px;">{{ $step->action }}</td>
                      <td class="text-sm" style="white-space: pre-wrap; max-width: 200px;">{{ $step->expected_result }}</td>
                      <td class="text-xs">{{ $step->test_data ?? '—' }}</td>
                      <td>
                        <select class="form-select form-select-sm step-status-select"
                          data-step-result-id="{{ $stepResult->id }}"
                          data-scenario-result-id="{{ $scenarioResult->id }}"
                          onchange="updateStepStatus(this)">
                          <option value="skipped" {{ $stepResult->status === 'skipped' ? 'selected' : '' }}>⏭ Skip</option>
                          <option value="passed" {{ $stepResult->status === 'passed' ? 'selected' : '' }}>✅ Pass</option>
                          <option value="failed" {{ $stepResult->status === 'failed' ? 'selected' : '' }}>❌ Fail</option>
                          <option value="blocked" {{ $stepResult->status === 'blocked' ? 'selected' : '' }}>🚫 Blocked</option>
                        </select>
                      </td>
                      <td>
                        <button type="button" class="btn btn-xs btn-outline-secondary" onclick="showStepDetail({{ $stepResult->id }})">
                          <i class="fa fa-comment"></i>
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            {{-- Scenario Overall Result --}}
            <div class="border-top pt-3">
              <div class="row align-items-end">
                <div class="col-md-3">
                  <label class="form-label text-xs font-weight-bold">Hasil Scenario</label>
                  <select class="form-select form-select-sm scenario-status-select"
                    data-scenario-result-id="{{ $scenarioResult->id }}"
                    onchange="updateScenarioStatus(this)">
                    <option value="skipped" {{ $scenarioResult->status === 'skipped' ? 'selected' : '' }}>⏭ Skipped</option>
                    <option value="passed" {{ $scenarioResult->status === 'passed' ? 'selected' : '' }}>✅ Passed</option>
                    <option value="failed" {{ $scenarioResult->status === 'failed' ? 'selected' : '' }}>❌ Failed</option>
                    <option value="blocked" {{ $scenarioResult->status === 'blocked' ? 'selected' : '' }}>🚫 Blocked</option>
                  </select>
                </div>
                <div class="col-md-5">
                  <label class="form-label text-xs">Actual Result / Catatan</label>
                  <input type="text" class="form-control form-control-sm scenario-notes"
                    data-scenario-result-id="{{ $scenarioResult->id }}"
                    value="{{ $scenarioResult->actual_result ?? '' }}"
                    placeholder="Apa yang sebenarnya terjadi..."
                    onblur="updateScenarioNotes(this)">
                </div>
                <div class="col-md-4">
                  <button type="button" class="btn btn-sm btn-outline-success" onclick="autoSetScenarioStatus({{ $scenarioResult->id }})">
                    <i class="fa fa-magic me-1"></i> Auto-set dari Steps
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    @endforeach
  </div>
@endsection

@push('scripts')
<script>
var csrfToken = '{{ csrf_token() }}';
var updateStepUrl = '{{ route("admin.manual-testing.executions.step-result", [$project, $execution]) }}';
var updateScenarioUrl = '{{ route("admin.manual-testing.executions.scenario-result", [$project, $execution]) }}';

function updateStepStatus(select) {
  var stepResultId = select.getAttribute('data-step-result-id');
  var status = select.value;

  fetch(updateStepUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    body: JSON.stringify({ step_result_id: stepResultId, status: status })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      highlightRow(select.closest('tr'), status);
    }
  });
}

function showStepDetail(stepResultId) {
  Swal.fire({
    title: 'Detail Step',
    html:
      '<div class="text-start">' +
      '<label class="form-label text-sm">Actual Result</label>' +
      '<textarea id="swal-actual" class="form-control mb-2" rows="3" placeholder="Apa yang sebenarnya terjadi..."></textarea>' +
      '<label class="form-label text-sm">Notes</label>' +
      '<textarea id="swal-notes" class="form-control" rows="2" placeholder="Catatan tambahan..."></textarea>' +
      '</div>',
    showCancelButton: true,
    confirmButtonText: 'Simpan',
    cancelButtonText: 'Batal',
    preConfirm: function() {
      return {
        actual_result: document.getElementById('swal-actual').value,
        notes: document.getElementById('swal-notes').value
      };
    }
  }).then(function(result) {
    if (result.isConfirmed) {
      fetch(updateStepUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({
          step_result_id: stepResultId,
          status: document.querySelector('[data-step-result-id="' + stepResultId + '"]').value,
          actual_result: result.value.actual_result,
          notes: result.value.notes
        })
      });
    }
  });
}

function updateScenarioStatus(select) {
  var scenarioResultId = select.getAttribute('data-scenario-result-id');
  var status = select.value;
  var notesInput = document.querySelector('.scenario-notes[data-scenario-result-id="' + scenarioResultId + '"]');

  fetch(updateScenarioUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    body: JSON.stringify({
      scenario_result_id: scenarioResultId,
      status: status,
      actual_result: notesInput ? notesInput.value : null
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    if (data.success) {
      updateBadge(scenarioResultId, status);
      updateProgress();
    }
  });
}

function updateScenarioNotes(input) {
  var scenarioResultId = input.getAttribute('data-scenario-result-id');
  var statusSelect = document.querySelector('.scenario-status-select[data-scenario-result-id="' + scenarioResultId + '"]');

  fetch(updateScenarioUrl, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
    body: JSON.stringify({
      scenario_result_id: scenarioResultId,
      status: statusSelect.value,
      actual_result: input.value
    })
  });
}

function autoSetScenarioStatus(scenarioResultId) {
  var card = document.getElementById('scenario-card-' + scenarioResultId);
  var stepSelects = card.querySelectorAll('.step-status-select');
  var hasFailed = false;
  var hasBlocked = false;
  var allPassed = true;

  stepSelects.forEach(function(s) {
    if (s.value === 'failed') hasFailed = true;
    if (s.value === 'blocked') hasBlocked = true;
    if (s.value !== 'passed') allPassed = false;
  });

  var newStatus = 'skipped';
  if (hasFailed) newStatus = 'failed';
  else if (hasBlocked) newStatus = 'blocked';
  else if (allPassed) newStatus = 'passed';

  var scenarioSelect = card.querySelector('.scenario-status-select');
  scenarioSelect.value = newStatus;
  updateScenarioStatus(scenarioSelect);
}

function highlightRow(row, status) {
  row.classList.remove('table-success', 'table-danger', 'table-warning');
  if (status === 'passed') row.classList.add('table-success');
  else if (status === 'failed') row.classList.add('table-danger');
  else if (status === 'blocked') row.classList.add('table-warning');
}

function updateBadge(scenarioResultId, status) {
  var badge = document.getElementById('scenario-status-badge-' + scenarioResultId);
  if (!badge) return;
  badge.textContent = status.toUpperCase();
  badge.className = 'badge bg-gradient-' + getBadgeColor(status);
}

function getBadgeColor(status) {
  switch(status) {
    case 'passed': return 'success';
    case 'failed': return 'danger';
    case 'blocked': return 'warning';
    default: return 'secondary';
  }
}

function updateProgress() {
  var selects = document.querySelectorAll('.scenario-status-select');
  var total = selects.length;
  var done = 0;
  selects.forEach(function(s) { if (s.value !== 'skipped') done++; });
  var pct = total > 0 ? Math.round((done / total) * 100) : 0;
  document.getElementById('progressBar').style.width = pct + '%';
  document.getElementById('progressText').textContent = done + ' / ' + total + ' scenarios';
}

function confirmComplete() {
  Swal.fire({
    title: 'Selesaikan Testing?',
    text: 'Pastikan semua scenario sudah di-review hasilnya.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, Selesaikan',
    cancelButtonText: 'Batal'
  }).then(function(result) {
    if (result.isConfirmed) {
      document.getElementById('completeForm').submit();
    }
  });
}

// Init progress on load
document.addEventListener('DOMContentLoaded', function() {
  updateProgress();
  // Highlight existing statuses
  document.querySelectorAll('.step-status-select').forEach(function(s) {
    if (s.value !== 'skipped') highlightRow(s.closest('tr'), s.value);
  });
});
</script>
@endpush
