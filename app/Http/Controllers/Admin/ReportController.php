<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManualTestExecution;
use App\Models\TestProject;
use App\Models\TestRun;
use App\Models\TestSuite;
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

    /**
     * Print-friendly view for all test cases in a project, grouped by suite.
     */
    public function testCases(TestProject $project): View
    {
        $project->load(['testSuites.testCases', 'testCases' => fn ($q) => $q->whereNull('test_suite_id')]);

        return view('admin.reports.test-cases', compact('project'));
    }

    /**
     * Print-friendly view for test cases in a specific suite.
     */
    public function suiteTestCases(TestProject $project, TestSuite $suite): View
    {
        $suite->load('testCases');

        return view('admin.reports.suite-test-cases', compact('project', 'suite'));
    }
}
