<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TestCase;
use App\Models\TestProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestCaseController extends Controller
{
    public function create(TestProject $project): View
    {
        $project->load('testSuites');

        return view('admin.blackbox.cases.create', compact('project'));
    }

    public function store(Request $request, TestProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'method' => ['required', 'in:GET,POST,PUT,PATCH,DELETE'],
            'endpoint' => ['required', 'string', 'max:500'],
            'headers' => ['nullable', 'string'], // JSON string
            'body_params' => ['nullable', 'string'], // JSON string
            'expected_status' => ['required', 'integer', 'min:100', 'max:599'],
            'expected_contains' => ['nullable', 'string', 'max:500'],
            'expected_not_contains' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
            'test_suite_id' => ['nullable', 'exists:test_suites,id'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['test_project_id'] = $project->id;
        $validated['order'] = $project->testCases()->count() + 1;

        // Parse JSON fields
        $validated['headers'] = $this->parseJson($validated['headers'] ?? null);
        $validated['body_params'] = $this->parseJson($validated['body_params'] ?? null);

        $testCase = TestCase::create($validated);

        ActivityLog::record('created', $testCase, "Menambah test case: {$testCase->title}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Test case \"{$testCase->title}\" berhasil ditambahkan.");
    }

    public function edit(TestProject $project, TestCase $case): View
    {
        $project->load('testSuites');

        return view('admin.blackbox.cases.edit', compact('project', 'case'));
    }

    public function update(Request $request, TestProject $project, TestCase $case): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'method' => ['required', 'in:GET,POST,PUT,PATCH,DELETE'],
            'endpoint' => ['required', 'string', 'max:500'],
            'headers' => ['nullable', 'string'],
            'body_params' => ['nullable', 'string'],
            'expected_status' => ['required', 'integer', 'min:100', 'max:599'],
            'expected_contains' => ['nullable', 'string', 'max:500'],
            'expected_not_contains' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['headers'] = $this->parseJson($validated['headers'] ?? null);
        $validated['body_params'] = $this->parseJson($validated['body_params'] ?? null);

        $case->update($validated);

        ActivityLog::record('updated', $case, "Mengubah test case: {$case->title}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Test case \"{$case->title}\" berhasil diperbarui.");
    }

    public function destroy(TestProject $project, TestCase $case): RedirectResponse
    {
        $title = $case->title;
        $case->delete();

        ActivityLog::record('deleted', null, "Menghapus test case: {$title}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Test case \"{$title}\" berhasil dihapus.");
    }

    private function parseJson(?string $value): ?array
    {
        if (empty($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
