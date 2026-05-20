@extends('admin.template.main')

@section('title', 'Tambah Scenario')
@section('page_title', 'Tambah Scenario')

@section('content')
  <x-admin.page-header
    icon="fa-plus"
    icon-gradient="primary"
    title="Tambah Test Scenario"
    :description="'Buat skenario manual testing untuk: ' . $project->name"
  >
    <a href="{{ route('admin.manual-testing.scenarios.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  {{-- AI Generator --}}
  <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #8b5cf6 !important;">
    <div class="card-body py-3">
      <div class="d-flex align-items-center gap-2 mb-2">
        <i class="fa fa-wand-magic-sparkles" style="color: #8b5cf6;"></i>
        <strong class="text-sm">AI Generate</strong>
        <small class="text-muted">— Deskripsikan fitur yang ingin di-test, form akan terisi otomatis.</small>
      </div>
      <div class="d-flex gap-2">
        <textarea id="aiPrompt" class="form-control form-control-sm" rows="2" style="flex: 1;">Test fitur login dengan username dan password, termasuk validasi error ketika field kosong dan ketika credential salah</textarea>
        <button type="button" class="btn btn-sm align-self-end" style="background: #8b5cf6; color: white; white-space: nowrap;" onclick="generateWithAi()" id="btnGenerate">
          <i class="fa fa-wand-magic-sparkles me-1"></i> Generate
        </button>
      </div>
    </div>
  </div>

  <form action="{{ route('admin.manual-testing.scenarios.store', $project) }}" method="POST" id="scenarioForm">
    @csrf

    {{-- Scenario Info --}}
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header pb-0">
        <h6 class="mb-0"><i class="fa fa-info-circle me-1 text-primary"></i> Informasi Scenario</h6>
      </div>
      <div class="card-body">
        @include('admin.manual-testing.scenarios.form-info')
      </div>
    </div>

    {{-- Steps --}}
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-header pb-0 d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="fa fa-list-ol me-1 text-success"></i> Langkah-langkah Testing</h6>
        <button type="button" class="btn btn-sm btn-outline-success" onclick="addStep()">
          <i class="fa fa-plus me-1"></i> Tambah Step
        </button>
      </div>
      <div class="card-body" id="stepsContainer">
        @if (old('steps'))
          @foreach (old('steps') as $i => $step)
            @include('admin.manual-testing.scenarios.form-step', ['index' => $i, 'step' => $step])
          @endforeach
        @else
          @include('admin.manual-testing.scenarios.form-step', ['index' => 0, 'step' => null])
        @endif
      </div>
    </div>

    <div class="mb-4">
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-save me-1"></i> Simpan Scenario
      </button>
    </div>
  </form>
@endsection

@push('scripts')
<script>
var stepIndex = {{ old('steps') ? count(old('steps')) : 1 }};

function addStep() {
  var container = document.getElementById('stepsContainer');
  var html = getStepHtml(stepIndex);
  container.insertAdjacentHTML('beforeend', html);
  stepIndex++;
}

function removeStep(index) {
  var el = document.getElementById('step-' + index);
  if (el && document.querySelectorAll('[id^="step-"]').length > 1) {
    el.remove();
    renumberSteps();
  } else {
    Swal.fire({ icon: 'warning', title: 'Minimal 1 step', text: 'Scenario harus memiliki minimal 1 langkah testing.' });
  }
}

function renumberSteps() {
  document.querySelectorAll('[id^="step-"]').forEach(function(el, i) {
    el.querySelector('.step-number').textContent = 'Step ' + (i + 1);
  });
}

function getStepHtml(index) {
  return '<div class="border rounded p-3 mb-3 position-relative" id="step-' + index + '">' +
    '<div class="d-flex justify-content-between align-items-center mb-2">' +
    '<strong class="step-number text-sm">Step ' + (index + 1) + '</strong>' +
    '<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep(' + index + ')"><i class="fa fa-times"></i></button>' +
    '</div>' +
    '<div class="row">' +
    '<div class="col-md-4 mb-2"><label class="form-label text-xs">Aksi / Langkah <span class="text-danger">*</span></label>' +
    '<textarea name="steps[' + index + '][action]" class="form-control" rows="2" placeholder="Apa yang harus dilakukan tester..." required></textarea></div>' +
    '<div class="col-md-4 mb-2"><label class="form-label text-xs">Expected Result <span class="text-danger">*</span></label>' +
    '<textarea name="steps[' + index + '][expected_result]" class="form-control" rows="2" placeholder="Hasil yang diharapkan..." required></textarea></div>' +
    '<div class="col-md-4 mb-2"><label class="form-label text-xs">Test Data</label>' +
    '<textarea name="steps[' + index + '][test_data]" class="form-control" rows="2" placeholder="Data input yang digunakan (opsional)..."></textarea></div>' +
    '</div></div>';
}

// ─── AI Generator ───

function generateWithAi() {
  var prompt = document.getElementById('aiPrompt').value.trim();
  if (!prompt) {
    Swal.fire({ icon: 'warning', title: 'Prompt kosong', text: 'Masukkan deskripsi fitur yang ingin di-test.' });
    return;
  }

  var btn = document.getElementById('btnGenerate');
  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Generating...';

  fetch('{{ route("admin.ai.generate-scenario") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({
      prompt: prompt,
      project_name: '{{ $project->name }}',
      base_url: '{{ $project->base_url }}',
      module: ''
    })
  })
  .then(function(r) { return r.json(); })
  .then(function(data) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-wand-magic-sparkles me-1"></i> Generate';

    if (!data.success) {
      Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
      return;
    }

    // Langsung isi ke form
    fillFormWithAi(data.data);
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-wand-magic-sparkles me-1"></i> Generate';
    Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi AI: ' + err.message });
  });
}

function fillFormWithAi(data) {
  // Isi field informasi scenario
  var titleInput = document.querySelector('input[name="title"]');
  var descInput = document.querySelector('textarea[name="description"]');
  var moduleInput = document.querySelector('input[name="module"]');
  var prioritySelect = document.querySelector('select[name="priority"]');
  var preconditionInput = document.querySelector('textarea[name="precondition"]');

  if (titleInput) titleInput.value = data.title || '';
  if (descInput) descInput.value = data.description || '';
  if (moduleInput) moduleInput.value = data.module || '';
  if (prioritySelect && data.priority) prioritySelect.value = data.priority;
  if (preconditionInput) preconditionInput.value = data.precondition || '';

  // Hapus steps lama, isi dengan steps dari AI
  var container = document.getElementById('stepsContainer');
  container.innerHTML = '';
  stepIndex = 0;

  data.steps.forEach(function(step, i) {
    container.insertAdjacentHTML('beforeend', getStepHtml(i));
    var stepEl = document.getElementById('step-' + i);
    var textareas = stepEl.querySelectorAll('textarea');
    if (textareas[0]) textareas[0].value = step.action || '';
    if (textareas[1]) textareas[1].value = step.expected_result || '';
    if (textareas[2]) textareas[2].value = step.test_data || '';
    stepIndex = i + 1;
  });

  // Scroll ke form
  document.getElementById('scenarioForm').scrollIntoView({ behavior: 'smooth', block: 'start' });

  Swal.fire({
    icon: 'success',
    title: 'Form terisi',
    text: data.steps.length + ' steps berhasil di-generate. Silakan review lalu simpan.',
    timer: 2500,
    showConfirmButton: false
  });
}
</script>
@endpush
