<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\TestProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestProjectController extends Controller
{
    public function index(): View
    {
        $projects = TestProject::withCount(['testCases', 'testRuns'])
            ->with('latestRun')
            ->latest()
            ->get();

        return view('admin.blackbox.projects.index', compact('projects'));
    }

    public function create(): View
    {
        return view('admin.blackbox.projects.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $project = TestProject::create($validated);

        ActivityLog::record('created', $project, "Menambah test project: {$project->name}");

        return redirect()->route('admin.blackbox.projects.index')
            ->with('success', "Project \"{$project->name}\" berhasil ditambahkan.");
    }

    public function show(TestProject $project): View
    {
        $project->load([
            'testSuites',
            'testCases.testSuite',
            'testRuns' => fn ($q) => $q->with(['user', 'testSuite'])->latest()->limit(10),
        ]);

        return view('admin.blackbox.projects.show', compact('project'));
    }

    public function edit(TestProject $project): View
    {
        return view('admin.blackbox.projects.edit', compact('project'));
    }

    public function update(Request $request, TestProject $project): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'base_url' => ['required', 'url', 'max:500'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $project->update($validated);

        ActivityLog::record('updated', $project, "Mengubah test project: {$project->name}");

        return redirect()->route('admin.blackbox.projects.index')
            ->with('success', "Project \"{$project->name}\" berhasil diperbarui.");
    }

    public function destroy(TestProject $project): RedirectResponse
    {
        $name = $project->name;
        $project->delete();

        ActivityLog::record('deleted', null, "Menghapus test project: {$name}");

        return redirect()->route('admin.blackbox.projects.index')
            ->with('success', "Project \"{$name}\" berhasil dihapus.");
    }
}
