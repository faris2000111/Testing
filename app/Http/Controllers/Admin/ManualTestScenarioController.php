<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ManualTestScenario;
use App\Models\ManualTestStep;
use App\Models\TestProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ManualTestScenarioController extends Controller
{
    public function index(TestProject $project): View
    {
        $scenarios = $project->manualScenarios()->withCount('steps')->get();

        return view('admin.manual-testing.scenarios.index', compact('project', 'scenarios'));
    }

    public function create(TestProject $project): View
    {
        return view('admin.manual-testing.scenarios.create', compact('project'));
    }

    public function store(Request $request, TestProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'module' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'precondition' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.action' => ['required', 'string', 'max:1000'],
            'steps.*.expected_result' => ['required', 'string', 'max:1000'],
            'steps.*.test_data' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['test_project_id'] = $project->id;
        $validated['order'] = $project->manualScenarios()->count() + 1;

        $steps = $validated['steps'];
        unset($validated['steps']);

        $scenario = ManualTestScenario::create($validated);

        // Create steps
        foreach ($steps as $index => $step) {
            ManualTestStep::create([
                'manual_test_scenario_id' => $scenario->id,
                'step_number' => $index + 1,
                'action' => $step['action'],
                'expected_result' => $step['expected_result'],
                'test_data' => $step['test_data'] ?? null,
                'order' => $index + 1,
            ]);
        }

        ActivityLog::record('created', $scenario, "Menambah manual test scenario: {$scenario->title}");

        return redirect()->route('admin.manual-testing.scenarios.index', $project)
            ->with('success', "Scenario \"{$scenario->title}\" berhasil ditambahkan.");
    }

    public function show(TestProject $project, ManualTestScenario $scenario): View
    {
        $scenario->load('steps');

        return view('admin.manual-testing.scenarios.show', compact('project', 'scenario'));
    }

    public function edit(TestProject $project, ManualTestScenario $scenario): View
    {
        $scenario->load('steps');

        return view('admin.manual-testing.scenarios.edit', compact('project', 'scenario'));
    }

    public function update(Request $request, TestProject $project, ManualTestScenario $scenario): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'module' => ['nullable', 'string', 'max:100'],
            'priority' => ['required', 'in:low,medium,high,critical'],
            'precondition' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'steps' => ['required', 'array', 'min:1'],
            'steps.*.action' => ['required', 'string', 'max:1000'],
            'steps.*.expected_result' => ['required', 'string', 'max:1000'],
            'steps.*.test_data' => ['nullable', 'string', 'max:500'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $steps = $validated['steps'];
        unset($validated['steps']);

        $scenario->update($validated);

        // Recreate steps
        $scenario->steps()->delete();
        foreach ($steps as $index => $step) {
            ManualTestStep::create([
                'manual_test_scenario_id' => $scenario->id,
                'step_number' => $index + 1,
                'action' => $step['action'],
                'expected_result' => $step['expected_result'],
                'test_data' => $step['test_data'] ?? null,
                'order' => $index + 1,
            ]);
        }

        ActivityLog::record('updated', $scenario, "Mengubah manual test scenario: {$scenario->title}");

        return redirect()->route('admin.manual-testing.scenarios.index', $project)
            ->with('success', "Scenario \"{$scenario->title}\" berhasil diperbarui.");
    }

    public function destroy(TestProject $project, ManualTestScenario $scenario): RedirectResponse
    {
        $title = $scenario->title;
        $scenario->delete();

        ActivityLog::record('deleted', null, "Menghapus manual test scenario: {$title}");

        return redirect()->route('admin.manual-testing.scenarios.index', $project)
            ->with('success', "Scenario \"{$title}\" berhasil dihapus.");
    }
}
