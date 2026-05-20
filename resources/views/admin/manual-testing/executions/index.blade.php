@extends('admin.template.main')

@section('title', 'Riwayat Execution')
@section('page_title', 'Riwayat Execution')

@section('content')
  <x-admin.page-header
    icon="fa-clock-rotate-left"
    icon-gradient="info"
    title="Riwayat Manual Test Execution"
    :description="'Riwayat testing manual untuk project: ' . $project->name"
  >
    <div class="d-flex gap-2">
      <a href="{{ route('admin.manual-testing.executions.create', $project) }}" class="btn btn-success btn-sm mb-0">
        <i class="fa fa-play me-1"></i> Mulai Testing Baru
      </a>
      <a href="{{ route('admin.manual-testing.scenarios.index', $project) }}" class="btn btn-outline-secondary btn-sm mb-0">
        <i class="fa fa-arrow-left me-1"></i> Kembali ke Scenarios
      </a>
    </div>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($executions->isEmpty())
        <x-admin.empty-state
          icon="fa-clock-rotate-left"
          title="Belum ada execution"
          description="Mulai testing manual untuk mencatat hasilnya."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0 js-datatable">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tester</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Environment</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Hasil</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($executions as $execution)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td class="text-sm">{{ $execution->created_at->format('d M Y H:i') }}</td>
                  <td class="text-sm">{{ $execution->user?->name ?? '—' }}</td>
                  <td class="text-sm">{{ $execution->environment ?? '—' }}</td>
                  <td>
                    <span class="badge bg-gradient-{{ $execution->getStatusBadge() }}">{{ $execution->getStatusLabel() }}</span>
                  </td>
                  <td>
                    @if ($execution->status === 'completed')
                      <span class="text-success font-weight-bold">{{ $execution->passed }}</span> /
                      <span class="text-danger font-weight-bold">{{ $execution->failed }}</span> /
                      <span class="text-muted">{{ $execution->skipped }}</span>
                      <small class="text-muted d-block">(pass/fail/skip)</small>
                    @else
                      <span class="text-muted">In progress</span>
                    @endif
                  </td>
                  <td>
                    @if ($execution->status === 'in_progress')
                      <a href="{{ route('admin.manual-testing.executions.execute', [$project, $execution]) }}" class="btn btn-sm btn-outline-success me-1" title="Lanjutkan">
                        <i class="fa fa-play"></i>
                      </a>
                    @endif
                    <a href="{{ route('admin.manual-testing.executions.show', [$project, $execution]) }}" class="btn btn-sm btn-outline-info me-1" title="Detail">
                      <i class="fa fa-eye"></i>
                    </a>
                    <form action="{{ route('admin.manual-testing.executions.destroy', [$project, $execution]) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal" data-title="Hapus execution ini?">
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
