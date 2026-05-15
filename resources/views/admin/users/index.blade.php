@extends('admin.template.main')

@section('title', 'User Manager')
@section('page_title', 'User Manager')

@section('content')
  <x-admin.page-header
    icon="fa-users"
    icon-gradient="primary"
    title="User Manager"
    description="Kelola user admin panel."
  >
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah User
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($users->isEmpty())
        <x-admin.empty-state
          icon="fa-users"
          title="Belum ada user"
          description="Tambahkan user untuk memberikan akses ke admin panel."
        />
      @else
        <div class="table-responsive">
          <table class="table align-items-center mb-0">
            <thead>
              <tr>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">#</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nama</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Username</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Role</th>
                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($users as $user)
                <tr>
                  <td class="ps-4">{{ $loop->iteration }}</td>
                  <td><span class="font-weight-bold">{{ $user->name }}</span></td>
                  <td><code>{{ $user->username }}</code></td>
                  <td>{{ $user->email ?? '-' }}</td>
                  <td>
                    @if ($user->role)
                      <span class="badge bg-gradient-{{ $user->role->is_superadmin ? 'primary' : 'secondary' }}">
                        {{ $user->role->label }}
                      </span>
                    @else
                      <span class="badge bg-gradient-dark">No Role</span>
                    @endif
                  </td>
                  <td>
                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary me-1">
                      <i class="fa fa-pen"></i>
                    </a>
                    @if ($user->id !== auth()->id())
                      <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline">
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
