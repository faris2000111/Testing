<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TestSuite extends Model
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

    public function testCases(): HasMany
    {
        return $this->hasMany(TestCase::class)->orderBy('order');
    }

    public function testRuns(): HasMany
    {
        return $this->hasMany(TestRun::class)->latest();
    }

    // ─── Helpers ───

    public function activeCases(): HasMany
    {
        return $this->testCases()->where('is_active', true);
    }
}
