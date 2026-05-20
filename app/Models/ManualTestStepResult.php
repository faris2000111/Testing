<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ManualTestStepResult extends Model
{
    protected $guarded = ['id'];

    // ─── Relationships ───

    public function scenarioResult(): BelongsTo
    {
        return $this->belongsTo(ManualTestScenarioResult::class, 'manual_test_scenario_result_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ManualTestStep::class, 'manual_test_step_id');
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
}
