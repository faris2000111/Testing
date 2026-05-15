@extends('admin.template.main')

@section('title', 'Role Manager')
@section('page_title', 'Role Manager')

@section('content')
  <x-admin.page-header
    icon="fa-shield-halved"
    icon-gradient="info"
    title="Role Manager"
    description="Kelola role dan hak akses menu."
  >
    <a href="{{ route('admin.roles.create') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah Role
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($roles->isEmpty())
        <x-admin.empty-state
          icon="fa-shield-halved"
          title="Belum ada role"
          description="Tambahkan role untuk mengatur hak akses menu."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Name</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tipe</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Jumlah User</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($roles as $role)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td>
                    <span class="font-weight-bold">{{ $role->label }}</span>
                  </td>
                  <td><code>{{ $role->name }}</code></td>
                  <td>
                    @if ($role->is_superadmin)
                      <span class="badge bg-gradient-primary">Superadmin</span>
                    @else
                      <span class="badge bg-gradient-secondary">Custom</span>
                    @endif
                  </td>
                  <td>{{ $role->users_count }} user</td>
                  <td>
                    <a href="{{ route('admin.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary me-1">
                      <i class="fa fa-pen"></i>
                    </a>
                    @if (! $role->is_superadmin)
                      <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    @endif
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
