<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TestProject;
use App\Models\TestSuite;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestSuiteController extends Controller
{
    public function create(TestProject $project): View
    {
        return view('admin.blackbox.suites.create', compact('project'));
    }

    public function store(Request $request, TestProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['test_project_id'] = $project->id;
        $validated['order'] = $project->testSuites()->count() + 1;

        $suite = TestSuite::create($validated);

        ActivityLog::record('created', $suite, "Menambah test suite: {$suite->name}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Suite \"{$suite->name}\" berhasil ditambahkan.");
    }

    public function edit(TestProject $project, TestSuite $suite): View
    {
        return view('admin.blackbox.suites.edit', compact('project', 'suite'));
    }

    public function update(Request $request, TestProject $project, TestSuite $suite): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $suite->update($validated);

        ActivityLog::record('updated', $suite, "Mengubah test suite: {$suite->name}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Suite \"{$suite->name}\" berhasil diperbarui.");
    }

    public function destroy(TestProject $project, TestSuite $suite): RedirectResponse
    {
        $name = $suite->name;
        $suite->delete();

        ActivityLog::record('deleted', null, "Menghapus test suite: {$name}");

        return redirect()->route('admin.blackbox.projects.show', $project)
            ->with('success', "Suite \"{$name}\" berhasil dihapus.");
    }
}
