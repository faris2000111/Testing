<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestRun extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'total_cases' => 'integer',
        'passed' => 'integer',
        'failed' => 'integer',
        'duration_ms' => 'float',
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

    public function results(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    // ─── Helpers ───

    public function getPassRate(): float
    {
        if ($this->total_cases === 0) {
            return 0;
        }

        return round(($this->passed / $this->total_cases) * 100, 1);
    }

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'completed' => $this->failed === 0 ? 'success' : 'warning',
            'running' => 'info',
            'failed' => 'danger',
            default => 'secondary',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'completed' => $this->failed === 0 ? 'All Passed' : 'Completed (with failures)',
            'running' => 'Running...',
            'failed' => 'Error',
            default => $this->status,
        };
    }
}
