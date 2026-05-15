@extends('admin.template.main')

@section('title', 'Menu Manager')
@section('page_title', 'Menu Manager')

@push('styles')
<style>
  .menu-list { list-style: none; padding: 0; margin: 0; }
  .menu-item {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1rem; border: 1px solid #e9ecef;
    border-radius: 0.5rem; margin-bottom: 0.5rem;
    background: #fff; transition: all 0.15s;
    cursor: grab;
  }
  .menu-item:hover { border-color: #4361ee; box-shadow: 0 2px 8px rgba(67,97,238,0.1); }
  .menu-item.is-dragging { opacity: 0.5; }
  .menu-item__icon {
    width: 36px; height: 36px; border-radius: 0.5rem;
    display: inline-flex; align-items: center; justify-content: center;
    color: #fff; font-size: 0.85rem; flex-shrink: 0;
  }
  .menu-item__info { flex: 1; min-width: 0; }
  .menu-item__label { font-weight: 600; font-size: 0.9rem; }
  .menu-item__route { font-size: 0.75rem; color: #6c757d; font-family: monospace; }
  .menu-item__actions { display: flex; gap: 0.35rem; }
  .menu-item__badge {
    font-size: 0.65rem; padding: 0.15rem 0.5rem; border-radius: 1rem;
    background: #e9ecef; color: #495057; font-weight: 600;
  }
  .menu-item__badge--inactive { background: #fee2e2; color: #dc2626; }
  .menu-children { padding-left: 2.5rem; margin-top: 0.25rem; margin-bottom: 0.5rem; }
  .menu-children .menu-item { border-left: 3px solid #4361ee; }
  .section-header {
    font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.05em; color: #6c757d; margin: 1.25rem 0 0.5rem; padding-left: 0.25rem;
  }
  .section-header:first-child { margin-top: 0; }
</style>
@endpush

@section('content')
  <x-admin.page-header
    icon="fa-bars"
    icon-gradient="primary"
    title="Menu Manager"
    description="Kelola menu sidebar admin. Drag untuk reorder, klik edit untuk ubah."
  >
    <a href="{{ route('admin.menus.create') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah Menu
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      @if ($menus->isEmpty())
        <x-admin.empty-state icon="fa-bars" title="Belum ada menu" description="Klik tombol Tambah Menu untuk membuat menu sidebar pertama." />
      @else
        @php
          $grouped = $menus->groupBy(fn ($m) => $m->section->name ?? 'Menu');
        @endphp
        @foreach ($grouped as $section => $items)
          <div class="section-header">{{ $section }}</div>
          <ul class="menu-list" data-section="{{ $section }}">
            @foreach ($items as $menu)
              <li class="menu-item" data-id="{{ $menu->id }}">
                <i class="fa fa-grip-vertical text-muted" style="cursor:grab;"></i>
                <span class="menu-item__icon bg-gradient-{{ $menu->icon_gradient }}">
                  <i class="fa {{ $menu->icon }}"></i>
                </span>
                <div class="menu-item__info">
                  <div class="menu-item__label">{{ $menu->label }}</div>
                  <div class="menu-item__route">
                    @if ($menu->route_name)
                      {{ $menu->route_name }}
                    @elseif ($menu->url)
                      {{ $menu->url }}
                    @else
                      <span class="text-warning">— parent only —</span>
                    @endif
                  </div>
                </div>
                @if (! $menu->is_active)
                  <span class="menu-item__badge menu-item__badge--inactive">Nonaktif</span>
                @endif
                <div class="menu-item__actions">
                  <a href="{{ route('admin.menus.edit', $menu) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="Edit">
                    <i class="fa fa-pen-to-square"></i>
                  </a>
                  <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="m-0">
                    @csrf @method('DELETE')
                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-swal" data-title="Hapus menu {{ $menu->label }}?">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </div>
              </li>
              @if ($menu->children->count())
                <div class="menu-children">
                  @foreach ($menu->children as $child)
                    <li class="menu-item" data-id="{{ $child->id }}">
                      <i class="fa fa-grip-vertical text-muted" style="cursor:grab;"></i>
                      <span class="menu-item__icon bg-gradient-{{ $child->icon_gradient }}">
                        <i class="fa {{ $child->icon }}"></i>
                      </span>
                      <div class="menu-item__info">
                        <div class="menu-item__label">{{ $child->label }}</div>
                        <div class="menu-item__route">{{ $child->route_name ?? $child->url ?? '—' }}</div>
                      </div>
                      @if (! $child->is_active)
                        <span class="menu-item__badge menu-item__badge--inactive">Nonaktif</span>
                      @endif
                      <div class="menu-item__actions">
                        <a href="{{ route('admin.menus.edit', $child) }}" class="btn btn-sm btn-outline-primary py-1 px-2"><i class="fa fa-pen-to-square"></i></a>
                        <form action="{{ route('admin.menus.destroy', $child) }}" method="POST" class="m-0">
                          @csrf @method('DELETE')
                          <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 btn-delete-swal"><i class="fa fa-trash"></i></button>
                        </form>
                      </div>
                    </li>
                  @endforeach
                </div>
              @endif
            @endforeach
          </ul>
        @endforeach
      @endif
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // Simple drag-and-drop reorder
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.menu-list').forEach(function (list) {
      var dragged = null;

      list.querySelectorAll('.menu-item').forEach(function (item) {
        item.setAttribute('draggable', 'true');

        item.addEventListener('dragstart', function (e) {
          dragged = this;
          this.classList.add('is-dragging');
          e.dataTransfer.effectAllowed = 'move';
        });

        item.addEventListener('dragend', function () {
          this.classList.remove('is-dragging');
          dragged = null;
          saveOrder(list);
        });

        item.addEventListener('dragover', function (e) {
          e.preventDefault();
          e.dataTransfer.dropEffect = 'move';
          if (dragged && dragged !== this) {
            var rect = this.getBoundingClientRect();
            var mid = rect.top + rect.height / 2;
            if (e.clientY < mid) {
              list.insertBefore(dragged, this);
            } else {
              list.insertBefore(dragged, this.nextSibling);
            }
          }
        });
      });
    });

    function saveOrder(list) {
      var items = [];
      list.querySelectorAll('.menu-item').forEach(function (el, i) {
        items.push({ id: parseInt(el.dataset.id), order: i });
      });
      fetch("{{ route('admin.menus.reorder') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': '{{ csrf_token() }}',
          'Accept': 'application/json'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ items: items })
      });
    }
  });
</script>
@endpush
