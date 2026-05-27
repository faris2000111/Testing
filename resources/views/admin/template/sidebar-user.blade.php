{{-- Shared user footer partial (used by both desktop and mobile sidebars) --}}
@auth
  @php $user = auth()->user(); @endphp
  <div class="admin-sidenav__user">
    <span class="admin-sidenav__user-avatar">
      {{ mb_strtoupper(mb_substr((string) ($user->name ?: $user->email), 0, 1)) }}
    </span>
    <div class="admin-sidenav__user-meta">
      <strong>{{ $user->name ?? 'Administrator' }}</strong>
      @if ($user->email)
        <small>{{ $user->email }}</small>
      @endif
    </div>
  </div>
  <form action="{{ route('logout') }}" method="POST" class="m-0 mt-2">
    @csrf
    <button type="submit" class="admin-sidenav__logout">
      <i class="fa fa-right-from-bracket me-1"></i> Keluar
    </button>
  </form>
@endauth
