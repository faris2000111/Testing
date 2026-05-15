<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('menu_sections', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Replace section string + section_order with section_id FK
        Schema::table('admin_menus', function (Blueprint $table) {
            $table->foreignId('section_id')->nullable()->after('section_order')->constrained('menu_sections')->nullOnDelete();
        });

        // Migrate existing data: create sections from existing section names
        $sections = \App\Models\AdminMenu::select('section', 'section_order')
            ->distinct()
            ->whereNotNull('section')
            ->get();

        foreach ($sections as $s) {
            $section = \App\Models\MenuSection::create([
                'name' => $s->section,
                'order' => $s->section_order ?? 0,
            ]);

            \App\Models\AdminMenu::where('section', $s->section)
                ->update(['section_id' => $section->id]);
        }

        // Drop old columns
        Schema::table('admin_menus', function (Blueprint $table) {
            $table->dropIndex(['section_order', 'section', 'order']);
            $table->dropColumn(['section', 'section_order']);
            $table->index(['section_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::table('admin_menus', function (Blueprint $table) {
            $table->dropIndex(['section_id', 'order']);
            $table->dropConstrainedForeignId('section_id');
            $table->string('section')->default('Menu')->after('route_name');
            $table->integer('section_order')->default(0)->after('section');
            $table->index(['section_order', 'section', 'order']);
        });

        Schema::dropIfExists('menu_sections');
    }
};
