<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Manual test scenarios
        Schema::create('manual_test_scenarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('module')->nullable(); // e.g. "Login", "Checkout", "Profile"
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->string('precondition')->nullable(); // what must be true before testing
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Steps within a manual test scenario
        Schema::create('manual_test_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_test_scenario_id')->constrained()->cascadeOnDelete();
            $table->integer('step_number');
            $table->text('action'); // what the tester should do
            $table->text('expected_result'); // what should happen
            $table->text('test_data')->nullable(); // input data to use
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Manual test execution sessions
        Schema::create('manual_test_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['in_progress', 'completed'])->default('in_progress');
            $table->integer('total_scenarios')->default(0);
            $table->integer('passed')->default(0);
            $table->integer('failed')->default(0);
            $table->integer('skipped')->default(0);
            $table->text('notes')->nullable();
            $table->string('environment')->nullable(); // e.g. "Chrome 120", "Mobile Safari"
            $table->timestamps();
        });

        // Results per scenario in an execution
        Schema::create('manual_test_scenario_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_test_execution_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manual_test_scenario_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['passed', 'failed', 'skipped', 'blocked'])->default('skipped');
            $table->text('notes')->nullable();
            $table->text('actual_result')->nullable(); // what actually happened
            $table->string('screenshot_path')->nullable();
            $table->timestamps();
        });

        // Results per step in a scenario result
        Schema::create('manual_test_step_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('manual_test_scenario_result_id')->constrained('manual_test_scenario_results')->cascadeOnDelete();
            $table->foreignId('manual_test_step_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['passed', 'failed', 'skipped', 'blocked'])->default('skipped');
            $table->text('actual_result')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_test_step_results');
        Schema::dropIfExists('manual_test_scenario_results');
        Schema::dropIfExists('manual_test_executions');
        Schema::dropIfExists('manual_test_steps');
        Schema::dropIfExists('manual_test_scenarios');
    }
};
