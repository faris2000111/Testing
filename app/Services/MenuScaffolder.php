<?php

namespace App\Services;

use App\Models\AdminMenu;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MenuScaffolder
{
    /**
     * Generate controller and views for a menu item.
     */
    public function scaffold(AdminMenu $menu): array
    {
        $generated = [];

        $controllerPath = $this->generateController($menu);
        if ($controllerPath) {
            $generated[] = $controllerPath;
        }

        $viewPaths = $this->generateViews($menu);
        $generated = array_merge($generated, $viewPaths);

        return $generated;
    }

    private function generateController(AdminMenu $menu): ?string
    {
        $studly = Str::studly($menu->slug);
        $controllerName = "{$studly}Controller";
        $path = app_path("Http/Controllers/Admin/{$controllerName}.php");

        if (File::exists($path)) {
            return null;
        }

        $slug = $menu->slug;
        $label = $menu->label;
        $viewFolder = "admin.{$slug}";
        $routePrefix = "admin.{$slug}";

        if ($menu->has_crud) {
            $content = $this->buildCrudController($controllerName, $label, $viewFolder, $routePrefix);
        } else {
            $content = $this->buildIndexController($controllerName, $viewFolder);
        }

        File::ensureDirectoryExists(dirname($path));
        File::put($path, $content);

        return $path;
    }

    private function generateViews(AdminMenu $menu): array
    {
        $viewDir = resource_path("views/admin/{$menu->slug}");
        File::ensureDirectoryExists($viewDir);

        $generated = [];
        $label = $menu->label;
        $slug = $menu->slug;
        $routePrefix = "admin.{$slug}";

        $indexPath = "{$viewDir}/index.blade.php";
        if (! File::exists($indexPath)) {
            File::put($indexPath, $this->buildIndexView($label, $routePrefix));
            $generated[] = $indexPath;
        }

        if ($menu->has_crud) {
            $createPath = "{$viewDir}/create.blade.php";
            if (! File::exists($createPath)) {
                File::put($createPath, $this->buildCreateView($label, $slug, $routePrefix));
                $generated[] = $createPath;
            }

            $editPath = "{$viewDir}/edit.blade.php";
            if (! File::exists($editPath)) {
                File::put($editPath, $this->buildEditView($label, $slug, $routePrefix));
                $generated[] = $editPath;
            }

            $formPath = "{$viewDir}/form.blade.php";
            if (! File::exists($formPath)) {
                File::put($formPath, $this->buildFormView());
                $generated[] = $formPath;
            }
        }

        return $generated;
    }

    // ─── Controller Builders ───

    private function buildIndexController(string $className, string $viewFolder): string
    {
        return '<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class '.$className.' extends Controller
{
    public function index(): View
    {
        return view(\''.$viewFolder.'.index\');
    }
}
';
    }

    private function buildCrudController(string $className, string $label, string $viewFolder, string $routePrefix): string
    {
        return '<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class '.$className.' extends Controller
{
    /**
     * Display a listing.
     */
    public function index(): View
    {
        // TODO: Ganti dengan query model yang sesuai
        // $items = YourModel::latest()->get();
        $items = collect();

        return view(\''.$viewFolder.'.index\', compact(\'items\'));
    }

    /**
     * Show the form for creating.
     */
    public function create(): View
    {
        return view(\''.$viewFolder.'.create\');
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // TODO: Tambahkan validasi sesuai field
            // \'name\' => [\'required\', \'string\', \'max:255\'],
        ]);

        // TODO: Ganti dengan model yang sesuai
        // $item = YourModel::create($validated);
        // ActivityLog::record(\'created\', $item, \'Menambah '.$label.' baru.\');

        return redirect()->route(\''.$routePrefix.'.index\')->with(\'success\', \''.$label.' berhasil ditambahkan.\');
    }

    /**
     * Show the form for editing.
     */
    public function edit(int $id): View
    {
        // TODO: Ganti dengan model yang sesuai
        // $item = YourModel::findOrFail($id);
        $item = null;

        return view(\''.$viewFolder.'.edit\', compact(\'item\'));
    }

    /**
     * Update the specified resource.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            // TODO: Tambahkan validasi sesuai field
            // \'name\' => [\'required\', \'string\', \'max:255\'],
        ]);

        // TODO: Ganti dengan model yang sesuai
        // $item = YourModel::findOrFail($id);
        // $item->update($validated);
        // ActivityLog::record(\'updated\', $item, \'Mengubah '.$label.'.\');

        return redirect()->route(\''.$routePrefix.'.index\')->with(\'success\', \''.$label.' berhasil diperbarui.\');
    }

    /**
     * Remove the specified resource.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Ganti dengan model yang sesuai
        // $item = YourModel::findOrFail($id);
        // $item->delete();
        // ActivityLog::record(\'deleted\', null, \'Menghapus '.$label.'.\');

        return redirect()->route(\''.$routePrefix.'.index\')->with(\'success\', \''.$label.' berhasil dihapus.\');
    }
}
';
    }

    // ─── View Builders ───

    private function buildIndexView(string $label, string $routePrefix): string
    {
        return '@extends(\'admin.template.main\')

@section(\'title\', \''.$label.'\')
@section(\'page_title\', \''.$label.'\')

@section(\'content\')
  <x-admin.page-header
    icon="fa-circle"
    icon-gradient="primary"
    title="'.$label.'"
    description="Kelola data '.$label.'."
  >
    {{-- Uncomment jika ada halaman create --}}
    {{-- <a href="{{ route(\''.$routePrefix.'.create\') }}" class="btn btn-primary btn-sm mb-0">
      <i class="fa fa-plus me-1"></i> Tambah
    </a> --}}
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <x-admin.empty-state
        icon="fa-inbox"
        title="Belum ada data"
        description="Data '.$label.' akan tampil di sini. Sesuaikan controller dan view ini."
      />

      {{-- Contoh tabel dengan DataTables:
      <div class="table-responsive">
        <table class="table js-datatable">
          <thead>
            <tr>
              <th>#</th>
              <th>Nama</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($items as $item)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->name }}</td>
                <td>
                  <a href="{{ route(\''.$routePrefix.'.edit\', $item) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fa fa-pen"></i>
                  </a>
                  <form action="{{ route(\''.$routePrefix.'.destroy\', $item) }}" method="POST" class="d-inline">
                    @csrf @method(\'DELETE\')
                    <button type="button" class="btn btn-sm btn-outline-danger btn-delete-swal">
                      <i class="fa fa-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      --}}
    </div>
  </div>
@endsection
';
    }

    private function buildCreateView(string $label, string $slug, string $routePrefix): string
    {
        return '@extends(\'admin.template.main\')

@section(\'title\', \'Tambah '.$label.'\')
@section(\'page_title\', \'Tambah '.$label.'\')

@section(\'content\')
  <x-admin.page-header
    icon="fa-plus"
    icon-gradient="primary"
    title="Tambah '.$label.'"
    description="Buat data '.$label.' baru."
  >
    <a href="{{ route(\''.$routePrefix.'.index\') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route(\''.$routePrefix.'.store\') }}" method="POST">
        @csrf
        @include(\'admin.'.$slug.'.form\')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
';
    }

    private function buildEditView(string $label, string $slug, string $routePrefix): string
    {
        return '@extends(\'admin.template.main\')

@section(\'title\', \'Edit '.$label.'\')
@section(\'page_title\', \'Edit '.$label.'\')

@section(\'content\')
  <x-admin.page-header
    icon="fa-pen-to-square"
    icon-gradient="warning"
    title="Edit '.$label.'"
    description="Ubah data '.$label.'."
  >
    <a href="{{ route(\''.$routePrefix.'.index\') }}" class="btn btn-outline-secondary btn-sm mb-0">
      <i class="fa fa-arrow-left me-1"></i> Kembali
    </a>
  </x-admin.page-header>

  <div class="card border-0 shadow-sm">
    <div class="card-body">
      <form action="{{ route(\''.$routePrefix.'.update\', $item) }}" method="POST">
        @csrf
        @method(\'PUT\')
        @include(\'admin.'.$slug.'.form\')
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">
            <i class="fa fa-save me-1"></i> Simpan Perubahan
          </button>
        </div>
      </form>
    </div>
  </div>
@endsection
';
    }

    private function buildFormView(): string
    {
        return '{{-- TODO: Sesuaikan field form dengan kolom database --}}
@php $isEdit = isset($item); @endphp

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label">Nama <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control @error(\'name\') is-invalid @enderror"
           value="{{ old(\'name\', $isEdit ? $item->name : \'\') }}" required>
    @error(\'name\')<div class="invalid-feedback">{{ $message }}</div>@enderror
  </div>

  {{-- Tambahkan field lain di sini --}}
</div>
';
    }
}
