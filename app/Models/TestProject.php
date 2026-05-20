<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestProject extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class)->orderBy('order');
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class)->latest();
    }

    public function manualScenarios(): HasMany
    {
        return $this->hasMany(ManualTestScenario::class)->orderBy('order');
    }

    public function manualExecutions(): HasMany
    {
        return $this->hasMany(ManualTestExecution::class)->latest();
    }

    // ─── Helpers ───

    public function getFullUrl(string $endpoint): string
    {
        return rtrim($this->base_url, '/') . '/' . ltrim($endpoint, '/');
    }

    public function activeCases(): HasMany
    {
        return $this->testCases()->where('is_active', true);
    }

    public function latestRun()
    {
        return $this->hasOne(TestRun::class)->latest();
    }
}
