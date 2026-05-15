<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_superadmin' => 'boolean',
    ];

    // ─── Relationships ───

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(AdminMenu::class, 'role_menu');
    }

    // ─── Helpers ───

    /**
     * Check if this role has access to a specific menu.
     */
    public function hasMenuAccess(AdminMenu $menu): bool
    {
        if ($this->is_superadmin) {
            return true;
        }

        return $this->menus()->where('admin_menu_id', $menu->id)->exists();
    }

    /**
     * Sync menu access for this role.
     */
    public function syncMenus(array $menuIds): void
    {
        $this->menus()->sync($menuIds);
    }
}
