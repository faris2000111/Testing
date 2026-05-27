@php
    use App\Models\AdminMenu;
    use Illuminate\Support\Facades\Schema;

    $sitePublicUrl = url('/');

    // Load dynamic menus from database, filtered by user role
    $menuTree = Schema::hasTable('admin_menus') ? AdminMenu::getMenuTree(auth()->user()) : [];
@endphp

{{-- Desktop Sidebar (xl+) --}}
@include('admin.template.sidebar-desktop')

{{-- Mobile Sidebar (<xl) --}}
@include('admin.template.sidebar-mobile')
