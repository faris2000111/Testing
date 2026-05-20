<div class="border rounded p-3 mb-3 position-relative" id="step-{{ $index }}">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <strong class="step-number text-sm">Step {{ $index + 1 }}</strong>
    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeStep({{ $index }})">
      <i class="fa fa-times"></i>
    </button>
  </div>
  <div class="row">
    <div class="col-md-4 mb-2">
      <label class="form-label text-xs">Aksi / Langkah <span class="text-danger">*</span></label>
      <textarea name="steps[{{ $index }}][action]" class="form-control" rows="2"
        placeholder="Apa yang harus dilakukan tester..." required>{{ $step['action'] ?? '' }}</textarea>
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label text-xs">Expected Result <span class="text-danger">*</span></label>
      <textarea name="steps[{{ $index }}][expected_result]" class="form-control" rows="2"
        placeholder="Hasil yang diharapkan..." required>{{ $step['expected_result'] ?? '' }}</textarea>
    </div>
    <div class="col-md-4 mb-2">
      <label class="form-label text-xs">Test Data</label>
      <textarea name="steps[{{ $index }}][test_data]" class="form-control" rows="2"
        placeholder="Data input yang digunakan (opsional)...">{{ $step['test_data'] ?? '' }}</textarea>
    </div>
  </div>
</div>
