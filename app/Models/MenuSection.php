<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuSection extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'order' => 'integer',
    ];

    // ─── Relationships ───

    public function menus(): HasMany
    {
        return $this->hasMany(AdminMenu::class, 'section_id')->orderBy('order');
    }

    // ─── Scopes ───

    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}
