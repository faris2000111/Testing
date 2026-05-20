<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestResult extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'actual_status' => 'integer',
        'response_time_ms' => 'float',
    ];

    // ─── Relationships ───

    public function testRun(): BelongsTo
    {
        return $this->belongsTo(TestRun::class);
    }

    public function testCase(): BelongsTo
    {
        return $this->belongsTo(TestCase::class);
    }

    // ─── Helpers ───

    public function getStatusBadge(): string
    {
        return match ($this->status) {
            'passed' => 'success',
            'failed' => 'danger',
            'error' => 'warning',
            'skipped' => 'secondary',
            default => 'secondary',
        };
    }
}
