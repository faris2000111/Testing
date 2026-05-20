<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestCase extends Model
{
    protected $table = 'test_cases';

    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'headers' => 'array',
        'body_params' => 'array',
        'expected_status' => 'integer',
        'order' => 'integer',
    ];

    // ─── Relationships ───

    public function testProject(): BelongsTo
    {
        return $this->belongsTo(TestProject::class);
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(TestResult::class);
    }

    // ─── Helpers ───

    public function getMethodBadgeColor(): string
    {
        return match ($this->method) {
            'GET' => 'success',
            'POST' => 'primary',
            'PUT' => 'warning',
            'PATCH' => 'info',
            'DELETE' => 'danger',
            default => 'secondary',
        };
    }
}
