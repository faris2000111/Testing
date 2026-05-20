<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualTestScenario extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'order' => 'integer',
    ];

    // ─── Relationships ───

    public function testProject(): BelongsTo
    {
        return $this->belongsTo(TestProject::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(ManualTestStep::class)->orderBy('step_number');
    }

    public function scenarioResults(): HasMany
    {
        return $this->hasMany(ManualTestScenarioResult::class);
    }

    // ─── Helpers ───

    public function getPriorityBadgeColor(): string
    {
        return match ($this->priority) {
            'critical' => 'danger',
            'high' => 'warning',
            'medium' => 'info',
            'low' => 'secondary',
            default => 'secondary',
        };
    }

    public function getPriorityLabel(): string
    {
        return match ($this->priority) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => ucfirst($this->priority),
        };
    }
}
