<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualTestScenarioResult extends Model
{
    protected $guarded = ['id'];

    // ─── Relationships ───

    public function execution(): BelongsTo
    {
        return $this->belongsTo(ManualTestExecution::class, 'manual_test_execution_id');
    }

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(ManualTestScenario::class, 'manual_test_scenario_id');
    }

    public function stepResults(): HasMany
    {
        return $this->hasMany(ManualTestStepResult::class);
    }

    // ─── Helpers ───

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'passed' => 'success',
            'failed' => 'danger',
            'skipped' => 'secondary',
            'blocked' => 'warning',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'passed' => 'Passed',
            'failed' => 'Failed',
            'skipped' => 'Skipped',
            'blocked' => 'Blocked',
            default => ucfirst($this->status),
        };
    }
}
