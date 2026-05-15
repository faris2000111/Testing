# Laravel Admin Template

Starter template untuk project website dengan admin panel siap pakai.

## Fitur

- **Auth** — Login dengan username, role-based access
- **Dashboard** — Halaman utama admin dengan activity log
- **Dynamic Menu** — Sidebar menu yang bisa ditambah/hapus dari admin, auto-scaffold controller & views
- **Section Manager** — Kelola grup/section menu sidebar
- **Role Manager** — Buat role custom, atur hak akses menu per role
- **User Manager** — CRUD user dari admin panel
- **Site Settings** — Identitas, branding, kontak, social media, SEO, AI settings, tampilan, maintenance
- **AI Settings** — Toggle provider (Gemini/OpenRouter), API key, system prompt
- **Maintenance Mode** — Block non-admin user, custom animated page
- **Profile** — Edit nama, username, email, avatar
- **Backup Database** — Download backup (SQLite/MySQL) dari admin
- **Activity Log** — Catatan otomatis setiap aksi admin
- **Error Pages** — Custom 403, 404, 500, 503 dengan animasi
- **Public Layout** — Base layout untuk halaman publik (navbar, footer, SEO)

## Instalasi

```bash
# Clone / copy template
cp -r template/ nama-project/
cd nama-project

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Konfigurasi database di .env
# DB_CONNECTION=mysql
# DB_DATABASE=nama_db
# DB_USERNAME=root
# DB_PASSWORD=

# Migrate & seed
php artisan migrate:fresh --seed

# Storage link (untuk upload gambar)
php artisan storage:link

# Jalankan
php artisan serve
```

## Default Login

| Username | Password | Role |
|----------|----------|------|
| `admin` | `password` | Administrator (Superadmin) |

## Cara Pakai

### Menambah Menu Baru

1. Buka **Section Manager** → buat section baru jika perlu
2. Buka **Menu Manager** → klik "Tambah Menu"
3. Isi label, slug, icon, pilih section
4. Centang "Generate CRUD" jika butuh halaman create/edit
5. Simpan → controller dan views otomatis di-generate

### Menambah Role

1. Buka **Role Manager** → "Tambah Role"
2. Isi nama, label
3. Centang menu yang boleh diakses
4. Simpan

### Reset Template (untuk project baru)

```bash
php artisan template:reset
```

Ini akan menghapus semua file scaffold dan reset database ke kondisi awal.

## Struktur

```
app/
├── Console/Commands/     # Artisan commands
├── Http/
│   ├── Controllers/Admin/  # Admin controllers
│   ├── Middleware/          # CheckMenuAccess, CheckMaintenanceMode
├── Models/                 # Eloquent models
├── Providers/              # AdminMenuServiceProvider (dynamic routes)
├── Services/               # MenuScaffolder
├── View/Components/Admin/  # Blade components

resources/views/
├── admin/                  # Admin panel views
│   ├── template/           # Layout (main, sidebar, navbar, etc.)
│   ├── dashboard/
│   ├── menu/
│   ├── sections/
│   ├── roles/
│   ├── users/
│   ├── settings/
│   ├── profile/
│   └── password/
├── auth/                   # Login page
├── errors/                 # Custom error pages (403, 404, 500, 503)
├── layouts/                # Public frontend layout
│   ├── app.blade.php
│   └── partials/
└── welcome.blade.php
```

## Tech Stack

- Laravel 12
- Argon Dashboard (admin UI)
- Font Awesome 6
- SweetAlert2
- DataTables
- jQuery

## Public Frontend

Gunakan layout `layouts.app` untuk halaman publik:

```blade
@extends('layouts.app')

@section('title', 'Judul Halaman')

@section('content')
  <div class="container" style="padding: 4rem 0;">
    <h1>Hello World</h1>
  </div>
@endsection
```

## License

Private template.
