<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TestProject;
use Illuminate\View\View;

class ManualTestingController extends Controller
{
    /**
     * Landing page: list all projects for manual testing.
     */
    public function index(): View
    {
        $projects = TestProject::where('is_active', true)
            ->withCount(['manualScenarios', 'manualExecutions'])
            ->latest()
            ->get();

        return view('admin.manual-testing.index', compact('projects'));
    }
}
