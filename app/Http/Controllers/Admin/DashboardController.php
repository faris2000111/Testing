<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $recentActivity = ActivityLog::query()
            ->with(['user'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard.index', compact('recentActivity'));
    }
}
