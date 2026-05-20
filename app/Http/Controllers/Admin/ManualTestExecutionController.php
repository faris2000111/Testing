<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ManualTestExecution;
use App\Models\ManualTestScenarioResult;
use App\Models\ManualTestStepResult;
use App\Models\TestProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManualTestExecutionController extends Controller
{
    /**
     * List all executions for a project.
     */
    public function index(TestProject $project): View
    {
        $executions = $project->manualExecutions()
            ->with('user')
            ->withCount('scenarioResults')
            ->get();

        return view('admin.manual-testing.executions.index', compact('project', 'executions'));
    }

    /**
     * Start a new manual test execution.
     */
    public function create(TestProject $project): View
    {
        $scenarios = $project->manualScenarios()->where('is_active', true)->withCount('steps')->get();

        return view('admin.manual-testing.executions.create', compact('project', 'scenarios'));
    }

    /**
     * Store a new execution and redirect to the execute page.
     */
    public function store(Request $request, TestProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'environment' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'scenario_ids' => ['required', 'array', 'min:1'],
            'scenario_ids.*' => ['exists:manual_test_scenarios,id'],
        ]);

        $execution = ManualTestExecution::create([
            'test_project_id' => $project->id,
            'user_id' => auth()->id(),
            'status' => 'in_progress',
            'total_scenarios' => count($validated['scenario_ids']),
            'environment' => $validated['environment'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create scenario results (initially skipped)
        foreach ($validated['scenario_ids'] as $scenarioId) {
            $scenarioResult = ManualTestScenarioResult::create([
                'manual_test_execution_id' => $execution->id,
                'manual_test_scenario_id' => $scenarioId,
                'status' => 'skipped',
            ]);

            // Create step results for each step in the scenario
            $scenario = \App\Models\ManualTestScenario::with('steps')->find($scenarioId);
            if ($scenario) {
                foreach ($scenario->steps as $step) {
                    ManualTestStepResult::create([
                        'manual_test_scenario_result_id' => $scenarioResult->id,
                        'manual_test_step_id' => $step->id,
                        'status' => 'skipped',
                    ]);
                }
            }
        }

        ActivityLog::record('created', $execution, "Memulai manual test execution untuk: {$project->name}");

        return redirect()->route('admin.manual-testing.executions.execute', [$project, $execution])
            ->with('success', 'Execution dimulai. Silakan jalankan test secara manual.');
    }

    /**
     * The main execution page where tester records results step by step.
     */
    public function execute(TestProject $project, ManualTestExecution $execution): View
    {
        $execution->load([
            'scenarioResults.scenario.steps',
            'scenarioResults.stepResults.step',
        ]);

        return view('admin.manual-testing.executions.execute', compact('project', 'execution'));
    }

    /**
     * Update a step result via AJAX.
     */
    public function updateStepResult(Request $request, TestProject $project, ManualTestExecution $execution): JsonResponse
    {
        $validated = $request->validate([
            'step_result_id' => ['required', 'exists:manual_test_step_results,id'],
            'status' => ['required', 'in:passed,failed,skipped,blocked'],
            'actual_result' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $stepResult = ManualTestStepResult::findOrFail($validated['step_result_id']);
        $stepResult->update([
            'status' => $validated['status'],
            'actual_result' => $validated['actual_result'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json(['success' => true, 'step_result' => $stepResult]);
    }

    /**
     * Update a scenario result via AJAX.
     */
    public function updateScenarioResult(Request $request, TestProject $project, ManualTestExecution $execution): JsonResponse
    {
        $validated = $request->validate([
            'scenario_result_id' => ['required', 'exists:manual_test_scenario_results,id'],
            'status' => ['required', 'in:passed,failed,skipped,blocked'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'actual_result' => ['nullable', 'string', 'max:1000'],
        ]);

        $scenarioResult = ManualTestScenarioResult::findOrFail($validated['scenario_result_id']);
        $scenarioResult->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? null,
            'actual_result' => $validated['actual_result'] ?? null,
        ]);

        return response()->json(['success' => true, 'scenario_result' => $scenarioResult]);
    }

    /**
     * Complete the execution.
     */
    public function complete(Request $request, TestProject $project, ManualTestExecution $execution): RedirectResponse
    {
        // Calculate totals from scenario results
        $results = $execution->scenarioResults;
        $passed = $results->where('status', 'passed')->count();
        $failed = $results->where('status', 'failed')->count();
        $skipped = $results->whereIn('status', ['skipped', 'blocked'])->count();

        $execution->update([
            'status' => 'completed',
            'passed' => $passed,
            'failed' => $failed,
            'skipped' => $skipped,
        ]);

        ActivityLog::record('updated', $execution, "Menyelesaikan manual test execution: {$project->name} ({$passed}/{$execution->total_scenarios} passed)");

        return redirect()->route('admin.manual-testing.executions.show', [$project, $execution])
            ->with('success', 'Manual test execution selesai.');
    }

    /**
     * Show execution detail/report.
     */
    public function show(TestProject $project, ManualTestExecution $execution): View
    {
        $execution->load([
            'user',
            'scenarioResults.scenario',
            'scenarioResults.stepResults.step',
        ]);

        return view('admin.manual-testing.executions.show', compact('project', 'execution'));
    }

    /**
     * Delete an execution.
     */
    public function destroy(TestProject $project, ManualTestExecution $execution): RedirectResponse
    {
        $execution->delete();

        ActivityLog::record('deleted', null, "Menghapus manual test execution #{$execution->id}");

        return redirect()->route('admin.manual-testing.executions.index', $project)
            ->with('success', 'Execution berhasil dihapus.');
    }
}
