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

    /**
     * Get the title (from live test case or snapshot).
     */
    public function getTitle(): string
    {
        return $this->testCase?->title ?? $this->snapshot_title ?? '—';
    }

    /**
     * Get the method (from live test case or snapshot).
     */
    public function getMethod(): ?string
    {
        return $this->testCase?->method ?? $this->snapshot_method;
    }

    /**
     * Get the endpoint (from live test case or snapshot).
     */
    public function getEndpoint(): ?string
    {
        return $this->testCase?->endpoint ?? $this->snapshot_endpoint;
    }

    /**
     * Get the expected status (from live test case or snapshot).
     */
    public function getExpectedStatus(): ?int
    {
        return $this->testCase?->expected_status ?? $this->snapshot_expected_status;
    }

    /**
     * Get method badge color.
     */
    public function getMethodBadgeColor(): string
    {
        return match ($this->getMethod()) {
            'GET' => 'success',
            'POST' => 'primary',
            'PUT' => 'warning',
            'PATCH' => 'info',
            'DELETE' => 'danger',
            default => 'secondary',
        };
    }

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
