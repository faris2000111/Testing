<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualTestExecution;
use App\Models\TestProject;
use App\Models\TestRun;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Print-friendly view for a blackbox test run.
     */
    public function blackboxRun(TestProject $project, TestRun $run): View
    {
        $run->load(['results.testCase', 'user']);

        return view('admin.reports.blackbox-run', compact('project', 'run'));
    }

    /**
     * Print-friendly view for a manual test execution.
     */
    public function manualExecution(TestProject $project, ManualTestExecution $execution): View
    {
        $execution->load([
            'user',
            'scenarioResults.scenario',
            'scenarioResults.stepResults.step',
        ]);

        return view('admin.reports.manual-execution', compact('project', 'execution'));
    }
}
