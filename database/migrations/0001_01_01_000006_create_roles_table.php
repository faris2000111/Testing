<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // admin, editor, viewer, etc.
            $table->string('label');           // Display name
            $table->boolean('is_superadmin')->default(false); // auto-access all menus
            $table->timestamps();
        });

        // Pivot: role can access which menus
        Schema::create('role_menu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_menu_id')->constrained('admin_menus')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['role_id', 'admin_menu_id']);
        });

        // Add role_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });

        Schema::dropIfExists('role_menu');
        Schema::dropIfExists('roles');
    }
};
