@extends('admin.template.main')

@section('title', 'Mulai Manual Testing')
@section('page_title', 'Mulai Manual Testing')

@section('content')
  <x-admin.page-header
    icon="fa-play"
    icon-gradient="success"
    title="Mulai Manual Testing"
    :description="'Pilih scenario yang ingin di-test untuk project: ' . $project->name"
  >
    <a href="{{ route('admin.manual-testing.scenarios.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  @if ($scenarios->isEmpty())
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <x-admin.empty-state
          icon="fa-clipboard-check"
          title="Belum ada scenario aktif"
          description="Tambahkan test scenario terlebih dahulu sebelum memulai testing."
        />
      </div>
    </div>
  @else
    <form action="{{ route('admin.manual-testing.executions.store', $project) }}" method="POST">
      @csrf

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header pb-0">
          <h6 class="mb-0"><i class="fa fa-gear me-1 text-secondary"></i> Konfigurasi Execution</h6>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Environment / Browser</label>
              <input type="text" name="environment" class="form-control @error('environment') is-invalid @enderror"
                value="{{ old('environment') }}" placeholder="Contoh: Chrome 120, Mobile Safari, Firefox">
              @error('environment')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label">Catatan</label>
              <input type="text" name="notes" class="form-control @error('notes') is-invalid @enderror"
                value="{{ old('notes') }}" placeholder="Catatan tambahan (opsional)">
              @error('notes')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
          <div>
            <h6 class="mb-0"><i class="fa fa-clipboard-list me-1 text-primary"></i> Pilih Scenarios</h6>
            <small class="text-muted">Centang scenario yang ingin dijalankan.</small>
          </div>
          <div>
            <button type="button" class="btn btn-xs btn-outline-primary" onclick="toggleAll(true)">Pilih Semua</button>
            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleAll(false)">Hapus Semua</button>
          </div>
        </div>
        <div class="card-body">
          @error('scenario_ids')
            <div class="alert alert-danger">{{ $message }}</div>
          @enderror

          <div class="row">
            @foreach ($scenarios as $scenario)
              <div class="col-md-6 mb-2">
                <div class="form-check border rounded p-3">
                  <input class="form-check-input scenario-checkbox" type="checkbox" name="scenario_ids[]"
                    value="{{ $scenario->id }}" id="scenario-{{ $scenario->id }}"
                    {{ in_array($scenario->id, old('scenario_ids', [])) ? 'checked' : '' }}>
                  <label class="form-check-label w-100" for="scenario-{{ $scenario->id }}">
                    <strong>{{ $scenario->title }}</strong>
                    <div class="d-flex gap-2 mt-1">
                      <span class="badge bg-gradient-{{ $scenario->getPriorityBadgeColor() }}">{{ $scenario->getPriorityLabel() }}</span>
                      @if ($scenario->module)
                        <span class="badge bg-gradient-dark">{{ $scenario->module }}</span>
                      @endif
                      <span class="badge bg-gradient-secondary">{{ $scenario->steps_count }} steps</span>
                    </div>
                    @if ($scenario->description)
                      <small class="text-muted d-block mt-1">{{ Str::limit($scenario->description, 80) }}</small>
                    @endif
                  </label>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      </div>

      <div class="mb-4">
        <button type="submit" class="btn btn-success">
          <i class="fa fa-play me-1"></i> Mulai Testing
        </button>
      </div>
    </form>
  @endif
@endsection

@push('scripts')
<script>
function toggleAll(checked) {
  document.querySelectorAll('.scenario-checkbox').forEach(function(cb) {
    cb.checked = checked;
  });
}
</script>
@endpush
