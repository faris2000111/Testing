<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('admin_menus')->cascadeOnDelete();
            $table->string('label');
            $table->string('slug')->unique(); // ID unik: folder view, route prefix
            $table->string('icon')->default('fa-circle');
            $table->string('icon_gradient')->default('primary');
            $table->string('route_name')->nullable(); // auto-generated or manual
            $table->string('section')->default('Menu');
            $table->integer('section_order')->default(0);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_crud')->default(false); // apakah punya create/edit/form
            $table->boolean('is_system')->default(false); // menu bawaan, tidak bisa dihapus
            $table->timestamps();

            $table->index(['section_order', 'section', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_menus');
    }
};
