<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Projects / target websites
        Schema::create('test_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('base_url');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Individual test cases
        Schema::create('test_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('method')->default('GET'); // GET, POST, PUT, DELETE
            $table->string('endpoint'); // relative path e.g. /login
            $table->json('headers')->nullable();
            $table->json('body_params')->nullable(); // for POST/PUT
            $table->integer('expected_status')->default(200);
            $table->string('expected_contains')->nullable(); // text that should appear in response
            $table->string('expected_not_contains')->nullable(); // text that should NOT appear
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Test run sessions
        Schema::create('test_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['running', 'completed', 'failed'])->default('running');
            $table->integer('total_cases')->default(0);
            $table->integer('passed')->default(0);
            $table->integer('failed')->default(0);
            $table->float('duration_ms')->nullable(); // total duration in ms
            $table->timestamps();
        });

        // Individual test results per run
        Schema::create('test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_run_id')->constrained()->cascadeOnDelete();
            $table->foreignId('test_case_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['passed', 'failed', 'error', 'skipped'])->default('skipped');
            $table->integer('actual_status')->nullable();
            $table->text('response_body')->nullable();
            $table->text('error_message')->nullable();
            $table->float('response_time_ms')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_results');
        Schema::dropIfExists('test_runs');
        Schema::dropIfExists('test_cases');
        Schema::dropIfExists('test_projects');
    }
};
