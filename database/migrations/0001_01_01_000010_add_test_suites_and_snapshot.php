<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Test suites (grouping test cases within a project)
        Schema::create('test_suites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_project_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // e.g. "Login Tests", "Dashboard Tests"
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Add suite_id to test_cases
        Schema::table('test_cases', function (Blueprint $table) {
            $table->foreignId('test_suite_id')->nullable()->after('test_project_id')->constrained()->nullOnDelete();
        });

        // Add suite_id to test_runs (so we know which suite was run)
        Schema::table('test_runs', function (Blueprint $table) {
            $table->foreignId('test_suite_id')->nullable()->after('test_project_id')->constrained()->nullOnDelete();
        });

        // Add snapshot fields to test_results so history is preserved even if test case is deleted
        Schema::table('test_results', function (Blueprint $table) {
            // Drop the old foreign key and recreate as nullable with nullOnDelete
            $table->dropForeign(['test_case_id']);
            $table->foreignId('test_case_id')->nullable()->change();
            $table->foreign('test_case_id')->references('id')->on('test_cases')->nullOnDelete();

            // Snapshot fields
            $table->string('snapshot_title')->nullable()->after('test_case_id');
            $table->string('snapshot_method')->nullable()->after('snapshot_title');
            $table->string('snapshot_endpoint')->nullable()->after('snapshot_method');
            $table->integer('snapshot_expected_status')->nullable()->after('snapshot_endpoint');
        });
    }

    public function down(): void
    {
        Schema::table('test_results', function (Blueprint $table) {
            $table->dropColumn(['snapshot_title', 'snapshot_method', 'snapshot_endpoint', 'snapshot_expected_status']);
        });

        Schema::table('test_runs', function (Blueprint $table) {
            $table->dropConstrainedForeignId('test_suite_id');
        });

        Schema::table('test_cases', function (Blueprint $table) {
            $table->dropConstrainedForeignId('test_suite_id');
        });

        Schema::dropIfExists('test_suites');
    }
};
