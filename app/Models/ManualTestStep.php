<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ManualTestStep extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'step_number' => 'integer',
        'order' => 'integer',
    ];

    // ─── Relationships ───

    public function scenario(): BelongsTo
    {
        return $this->belongsTo(ManualTestScenario::class, 'manual_test_scenario_id');
    }

    public function stepResults(): HasMany
    {
        return $this->hasMany(ManualTestStepResult::class);
    }
}
