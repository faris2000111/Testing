@extends('admin.template.main')

@section('title', 'Blackbox Testing')
@section('page_title', 'Blackbox Testing')

@section('content')
  <x-admin.page-header
    icon="fa-vial"
    icon-gradient="info"
    title="Blackbox Testing"
    description="Kelola project dan jalankan blackbox testing pada website target."
  >
    <a href="{{ route('admin.blackbox.projects.create') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah Project
    </a>
  </x-admin.page-header>

  {{-- Quick Auto-Test Card --}}
  <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #3b82f6 !important;">
    <div class="card-body py-3">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <div class="d-flex align-items-center gap-2">
          <i class="fa fa-bolt text-primary"></i>
          <strong class="text-sm">Instant Multi-Role Auto-Test Website</strong>
          <small class="text-muted d-none d-md-inline">— Masukkan URL & kredensial akun per role (Admin, User, dll), AI akan menguji seluruh dashboard secara otomatis.</small>
        </div>
      </div>

      <div class="row g-2 mb-3">
        <div class="col-md-6">
          <label class="form-label text-xs mb-1">URL Target Website</label>
          <input type="url" id="quickTestUrl" class="form-control form-control-sm" placeholder="https://example.com" required>
        </div>
        <div class="col-md-6">
          <label class="form-label text-xs mb-1">Prompt / Instruksi Khusus (Opsional)</label>
          <input type="text" id="quickTestPrompt" class="form-control form-control-sm" placeholder="Uji dashboard admin, dashboard pengguna, dan fitur-fiturnya">
        </div>
      </div>

      <div class="mb-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
          <label class="form-label text-xs mb-0 fw-bold"><i class="fa fa-users me-1 text-info"></i> Akun Login & Role (Opsional — Bisa Tambah Banyak Akun)</label>
          <button type="button" class="btn btn-xs btn-outline-info py-0 px-2" onclick="addAccountRow()">
            <i class="fa fa-plus me-1"></i> Tambah Akun / Role
          </button>
        </div>
        <div id="accountsContainer">
          <!-- Row 1: Admin -->
          <div class="row g-2 mb-2 account-row">
            <div class="col-md-3">
              <input type="text" class="form-control form-control-sm account-role" placeholder="Role (e.g. Admin / Superadmin)" value="Admin">
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control form-control-sm account-username" placeholder="Username / Email Login">
            </div>
            <div class="col-md-4">
              <input type="password" class="form-control form-control-sm account-password" placeholder="Password">
            </div>
            <div class="col-md-1">
              <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1" onclick="removeAccountRow(this)" title="Hapus"><i class="fa fa-trash"></i></button>
            </div>
          </div>
          <!-- Row 2: User -->
          <div class="row g-2 mb-2 account-row">
            <div class="col-md-3">
              <input type="text" class="form-control form-control-sm account-role" placeholder="Role (e.g. Pengguna / User)" value="Pengguna">
            </div>
            <div class="col-md-4">
              <input type="text" class="form-control form-control-sm account-username" placeholder="Username / Email Login">
            </div>
            <div class="col-md-4">
              <input type="password" class="form-control form-control-sm account-password" placeholder="Password">
            </div>
            <div class="col-md-1">
              <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1" onclick="removeAccountRow(this)" title="Hapus"><i class="fa fa-trash"></i></button>
            </div>
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end mt-3">
        <button type="button" class="btn btn-primary btn-sm mb-0 px-4" id="btnQuickTest" onclick="runQuickTest()">
          <i class="fa fa-play me-1"></i> Jalankan Auto-Test Multirole
        </button>
      </div>
    </div>
  </div>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($projects->isEmpty())
        <x-admin.empty-state
          icon="fa-vial"
          title="Belum ada project"
          description="Tambahkan project website yang ingin kamu testing."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0 js-datatable">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Project</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Base URL</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Test Cases</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Last Run</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($projects as $project)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="font-weight-bold text-decoration-none">
                      {{ $project->name }}
                    </a>
                  </td>
                  <td><code class="text-xs">{{ $project->base_url }}</code></td>
                  <td>
                    <span class="badge bg-gradient-secondary">{{ $project->test_cases_count }} cases</span>
                  </td>
                  <td>
                    @if ($project->latestRun)
                      <span class="text-xs">{{ $project->latestRun->created_at->diffForHumans() }}</span>
                    @else
                      <span class="text-xs text-muted">Belum pernah</span>
                    @endif
                  </td>
                  <td>
                    @if ($project->is_active)
                      <span class="badge bg-gradient-success">Active</span>
                    @else
                      <span class="badge bg-gradient-secondary">Inactive</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.blackbox.projects.show', $project) }}" class="btn btn-sm btn-outline-info me-1" title="Detail">
                      <i class="fa fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.blackbox.projects.edit', $project) }}" class="btn btn-sm btn-outline-primary me-1" title="Edit">
                      <i class="fa fa-pen"></i>
                    </a>
                    <form action="{{ route('admin.blackbox.projects.destroy', $project) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal" data-title="Hapus project {{ $project->name }}?">
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
@endsection

@push('scripts')
<script>
function addAccountRow() {
  var container = document.getElementById('accountsContainer');
  var div = document.createElement('div');
  div.className = 'row g-2 mb-2 account-row';
  div.innerHTML = `
    <div class="col-md-3">
      <input type="text" class="form-control form-control-sm account-role" placeholder="Role (e.g. Editor, Manager)">
    </div>
    <div class="col-md-4">
      <input type="text" class="form-control form-control-sm account-username" placeholder="Username / Email Login">
    </div>
    <div class="col-md-4">
      <input type="password" class="form-control form-control-sm account-password" placeholder="Password">
    </div>
    <div class="col-md-1">
      <button type="button" class="btn btn-outline-danger btn-sm w-100 py-1" onclick="removeAccountRow(this)" title="Hapus"><i class="fa fa-trash"></i></button>
    </div>
  `;
  container.appendChild(div);
}

function removeAccountRow(btn) {
  var rows = document.querySelectorAll('.account-row');
  if (rows.length > 1) {
    btn.closest('.account-row').remove();
  } else {
    Swal.fire({ icon: 'info', title: 'Info', text: 'Minimal menyisakan 1 baris akun.' });
  }
}

function runQuickTest() {
  var url = document.getElementById('quickTestUrl').value.trim();
  var prompt = document.getElementById('quickTestPrompt').value.trim();
  var btn = document.getElementById('btnQuickTest');

  if (!url) {
    Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan masukkan URL website target.' });
    return;
  }

  var accounts = [];
  document.querySelectorAll('.account-row').forEach(function(row) {
    var role = row.querySelector('.account-role').value.trim();
    var username = row.querySelector('.account-username').value.trim();
    var password = row.querySelector('.account-password').value.trim();
    if (username && password) {
      accounts.push({ role: role || 'User', username: username, password: password });
    }
  });

  btn.disabled = true;
  btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> AI Generating & Testing Multirole...';

  fetch('{{ route("admin.blackbox.quick-test") }}', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': '{{ csrf_token() }}',
      'Accept': 'application/json'
    },
    body: JSON.stringify({ url: url, prompt: prompt, accounts: accounts })
  })
  .then(function(res) {
    if (!res.ok) {
      return res.json().then(function(err) { throw new Error(err.message || 'Terjadi kesalahan'); });
    }
    return res.json();
  })
  .then(function(data) {
    if (data.success) {
      Swal.fire({
        icon: 'success',
        title: 'Testing Multirole Selesai!',
        text: 'Total: ' + data.total + ' cases | Passed: ' + data.passed + ' | Failed: ' + data.failed,
        confirmButtonText: 'Lihat Laporan Detail'
      }).then(function() {
        window.location.href = data.redirect_url;
      });
    } else {
      throw new Error(data.message || 'Gagal menjalankan auto-test');
    }
  })
  .catch(function(err) {
    btn.disabled = false;
    btn.innerHTML = '<i class="fa fa-play me-1"></i> Jalankan Auto-Test Multirole';
    Swal.fire({ icon: 'error', title: 'Gagal', text: err.message });
  });
}
</script>
@endpush
