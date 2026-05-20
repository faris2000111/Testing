<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Test suites (grouping test cases within a project)
        Schema::create('test_suites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add suite_id to test_cases
        Schema::table('test_cases', function (Blueprint $table) {
            $table->unsignedBigInteger('test_suite_id')->nullable()->after('test_project_id');
            $table->foreign('test_suite_id')->references('id')->on('test_suites')->nullOnDelete();
        });

        // Add suite_id to test_runs
        Schema::table('test_runs', function (Blueprint $table) {
            $table->unsignedBigInteger('test_suite_id')->nullable()->after('test_project_id');
            $table->foreign('test_suite_id')->references('id')->on('test_suites')->nullOnDelete();
        });

        // Add snapshot fields to test_results
        Schema::table('test_results', function (Blueprint $table) {
            $table->string('snapshot_title')->nullable()->after('test_case_id');
            $table->string('snapshot_method', 10)->nullable()->after('snapshot_title');
            $table->string('snapshot_endpoint')->nullable()->after('snapshot_method');
            $table->integer('snapshot_expected_status')->nullable()->after('snapshot_endpoint');
        });

        // Change test_case_id foreign key from cascadeOnDelete to nullOnDelete
        // Drop old FK, make nullable, add new FK
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['test_case_id']);
        });

        // Make column nullable using raw SQL (no doctrine/dbal needed)
        DB::statement('ALTER TABLE test_results MODIFY test_case_id BIGINT UNSIGNED NULL');

        Schema::table('test_results', function (Blueprint $table) {
            $table->foreign('test_case_id')->references('id')->on('test_cases')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropForeign(['test_case_id']);
            $table->dropColumn(['snapshot_title', 'snapshot_method', 'snapshot_endpoint', 'snapshot_expected_status']);
        });

        DB::statement('ALTER TABLE test_results MODIFY test_case_id BIGINT UNSIGNED NOT NULL');

        Schema::table('test_results', function (Blueprint $table) {
            $table->foreign('test_case_id')->references('id')->on('test_cases')->cascadeOnDelete();
        });

        Schema::table('test_runs', function (Blueprint $table) {
            $table->dropForeign(['test_suite_id']);
            $table->dropColumn('test_suite_id');
        });

        Schema::table('test_cases', function (Blueprint $table) {
            $table->dropForeign(['test_suite_id']);
            $table->dropColumn('test_suite_id');
        });

        Schema::dropIfExists('test_suites');
    }
};
