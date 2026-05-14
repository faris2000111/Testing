@extends('admin.template.main')

@section('title', 'Admin Dashboard')
@section('page_title', 'Dashboard')

@section('content')
  <x-admin.page-header
    icon="fa-gauge-high"
    icon-gradient="primary"
    eyebrow="Selamat datang"
    title="Dashboard"
    description="Ringkasan dan aktivitas terbaru di panel admin."
  >
    <a href="{{ url('/') }}" target="_blank" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-arrow-up-right-from-square me-1"></i> Buka Website
    </a>
  </x-admin.page-header>

  {{-- Quick Info Cards --}}
  <div class="row g-3 mb-4">
    <div class="col-xl-4 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-primary">
            <i class="fa fa-gear"></i>
          </span>
          <div>
            <span class="stat-card__label">Project Name</span>
            <strong class="stat-card__value">{{ $siteSetting?->project_name ?? config('app.name') }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-info">
            <i class="fa fa-globe"></i>
          </span>
          <div>
            <span class="stat-card__label">Site Name</span>
            <strong class="stat-card__value">{{ $siteSetting?->site_name ?? config('app.name') }}</strong>
          </div>
        </div>
      </div>
    </div>
    <div class="col-xl-4 col-sm-6">
      <div class="card">
        <div class="card-body d-flex align-items-center gap-3 p-3">
          <span class="stat-card__icon bg-gradient-success">
            <i class="fa fa-user"></i>
          </span>
          <div>
            <span class="stat-card__label">Logged in as</span>
            <strong class="stat-card__value">{{ auth()->user()->name ?? auth()->user()->email }}</strong>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Recent Activity --}}
  <div class="row">
    <div class="col-12 mb-4">
      <div class="card admin-card">
        <div class="card-header pb-0">
          <h6 class="mb-0"><i class="fa fa-clock-rotate-left me-1 text-secondary"></i> Aktivitas Admin Terbaru</h6>
          <small class="text-muted">Catatan otomatis dari setiap aksi di panel admin.</small>
        </div>
        <div class="card-body p-0">
          @if ($recentActivity->isEmpty())
            <x-admin.empty-state icon="fa-clock-rotate-left" title="Belum ada aktivitas" description="Setiap aksi yang kamu lakukan di admin akan tercatat di sini." />
          @else
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead class="text-uppercase text-xs text-muted">
                  <tr>
                    <th class="ps-3">Waktu</th>
                    <th>User</th>
                    <th>Jenis</th>
                    <th>Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($recentActivity as $log)
                    @php
                      $badge = match ($log->action) {
                        'created' => 'success',
                        'updated' => 'primary',
                        'deleted' => 'danger',
                        'password_changed' => 'warning',
                        default => 'secondary',
                      };
                    @endphp
                    <tr>
                      <td class="ps-3 text-sm">{{ $log->created_at?->format('d M H:i') }}</td>
                      <td class="text-sm">{{ $log->user?->name ?? '—' }}</td>
                      <td><span class="badge bg-{{ $badge }}">{{ $log->action_label }}</span></td>
                      <td class="text-sm">{{ $log->description ?? '—' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
