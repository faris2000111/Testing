<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualTestExecution extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'total_scenarios' => 'integer',
        'passed' => 'integer',
        'failed' => 'integer',
        'skipped' => 'integer',
    ];

    // ─── Relationships ───

    public function testProject(): BelongsTo
    {
        return $this->belongsTo(TestProject::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scenarioResults(): HasMany
    {
        return $this->hasMany(ManualTestScenarioResult::class);
    }

    // ─── Helpers ───

    public function getPassRate(): float
    {
        if ($this->total_scenarios === 0) {
            return 0;
        }

        return round(($this->passed / $this->total_scenarios) * 100, 1);
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'completed' => $this->failed === 0 ? 'success' : 'warning',
            'in_progress' => 'info',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'completed' => $this->failed === 0 ? 'All Passed' : 'Completed (with failures)',
            'in_progress' => 'In Progress',
            default => ucfirst($this->status),
        };
    }
}
