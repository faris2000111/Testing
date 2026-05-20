@extends('admin.template.main')

@section('title', 'Edit Scenario')
@section('page_title', 'Edit Scenario')

@section('content')
  <x-admin.page-header
    icon="fa-pen"
    icon-gradient="primary"
    title="Edit Test Scenario"
    :description="'Ubah skenario: ' . $scenario->title"
  >
    <a href="{{ route('admin.manual-testing.scenarios.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <form action="{{ route('admin.manual-testing.scenarios.update', [$project, $scenario]) }}" method="POST" id="scenarioForm">
    @csrf @method('PUT')

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
        @php $steps = old('steps', $scenario->steps->map(fn($s) => ['action' => $s->action, 'expected_result' => $s->expected_result, 'test_data' => $s->test_data])->toArray()); @endphp
        @foreach ($steps as $i => $step)
          @include('admin.manual-testing.scenarios.form-step', ['index' => $i, 'step' => $step])
        @endforeach
      </div>
    </div>

    <div class="mb-4">
      <button type="submit" class="btn btn-primary">
        <i class="fa fa-save me-1"></i> Simpan Perubahan
      </button>
    </div>
  </form>
@endsection

@push('scripts')
<script>
var stepIndex = {{ count($steps) }};

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
</script>
@endpush
