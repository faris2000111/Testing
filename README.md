# Admin Template Starter

Template starter untuk membuat website baru dengan admin panel yang sudah siap pakai. Dibangun dengan **Laravel 13**, **Tailwind CSS v4**, dan **Argon Dashboard**.

## Fitur

- ✅ Login / Logout authentication
- ✅ Admin Dashboard dengan activity log
- ✅ Halaman Settings (identitas, branding, kontak, social media, SEO, tampilan, maintenance)
- ✅ Ubah password admin
- ✅ Dark/Light mode toggle
- ✅ Responsive sidebar navigation
- ✅ DataTables integration
- ✅ SweetAlert2 notifications
- ✅ Activity logging otomatis

## Instalasi

```bash
# Clone / copy project
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database
php artisan migrate --seed
php artisan storage:link

# Development
composer dev
# atau manual:
php artisan serve
npm run dev
```

## Login Default

| Email | Password |
|-------|----------|
| admin@admin.com | password |

> ⚠️ Ganti password setelah login pertama kali!

## Struktur Admin

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   ├── DashboardController.php
│   │   ├── SiteSettingController.php
│   │   └── PasswordController.php
│   └── Auth/
│       └── LoginController.php
├── Models/
│   ├── ActivityLog.php
│   ├── SiteSetting.php
│   └── User.php
└── View/Components/Admin/
    ├── PageHeader.php
    └── EmptyState.php

resources/views/
├── admin/
│   ├── dashboard/index.blade.php
│   ├── password/edit.blade.php
│   ├── settings/edit.blade.php
│   └── template/
│       ├── main.blade.php (layout utama)
│       ├── head.blade.php
│       ├── sidebar.blade.php
│       ├── navbar.blade.php
│       ├── footer.blade.php
│       └── scripts.blade.php
├── auth/login.blade.php
└── components/admin/
    ├── page-header.blade.php
    └── empty-state.blade.php
```

## Cara Menambah Halaman Baru

1. Buat Controller di `app/Http/Controllers/Admin/`
2. Buat View di `resources/views/admin/nama-fitur/`
3. Tambah route di `routes/web.php` dalam group `admin`
4. Tambah link di sidebar: `resources/views/admin/template/sidebar.blade.php`

### Contoh:

```php
// routes/web.php (dalam group admin)
Route::resource('products', ProductController::class)->names('admin.products');
```

```php
// View extends layout
@extends('admin.template.main')
@section('title', 'Products')
@section('page_title', 'Products')
@section('content')
  {{-- konten di sini --}}
@endsection
```

## Tech Stack

- PHP 8.3+
- Laravel 13
- Tailwind CSS v4
- Vite 8
- Argon Dashboard (CSS/JS)
- jQuery + DataTables
- SweetAlert2
- Chart.js
- Font Awesome 6

## License

MIT
