<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminMenu extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'has_crud' => 'boolean',
        'is_system' => 'boolean',
        'order' => 'integer',
    ];

    // ─── Relationships ───

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('order');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_menu');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(MenuSection::class, 'section_id');
    }

    // ─── Scopes ───

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    // ─── Helpers ───

    /**
     * Get all menus grouped by section for sidebar rendering.
     * Filtered by user's role access.
     */
    public static function getMenuTree(?User $user = null): array
    {
        $menus = self::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('order'), 'section'])
            ->whereHas('section')
            ->get()
            ->sortBy(fn ($m) => ($m->section->order ?? 0) * 10000 + $m->order);

        // Filter menus based on user role
        if ($user && $user->role) {
            if (! $user->isSuperAdmin()) {
                $accessibleMenuIds = $user->role->menus()->pluck('admin_menu_id')->toArray();

                $menus = $menus->filter(function ($menu) use ($accessibleMenuIds) {
                    return in_array($menu->id, $accessibleMenuIds);
                });

                // Also filter children
                $menus->each(function ($menu) use ($accessibleMenuIds) {
                    $menu->setRelation(
                        'children',
                        $menu->children->filter(fn ($child) => in_array($child->id, $accessibleMenuIds))
                    );
                });
            }
        }

        $grouped = [];
        foreach ($menus as $menu) {
            $sectionName = $menu->section->name ?? 'Menu';
            if (! isset($grouped[$sectionName])) {
                $grouped[$sectionName] = [];
            }
            $grouped[$sectionName][] = $menu;
        }

        return $grouped;
    }

    /**
     * Resolve the URL for this menu item.
     */
    public function resolveUrl(): ?string
    {
        if ($this->route_name) {
            try {
                return route($this->route_name);
            } catch (\Exception) {
                return '#';
            }
        }

        return null;
    }

    /**
     * Check if this menu or any of its children is currently active.
     */
    public function isActive(): bool
    {
        if ($this->route_name && request()->routeIs($this->route_name . '*')) {
            return true;
        }

        // Also check slug-based route pattern
        if ($this->slug && request()->routeIs("admin.{$this->slug}.*")) {
            return true;
        }

        foreach ($this->children as $child) {
            if ($child->isActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the controller class name for this menu.
     */
    public function getControllerClass(): string
    {
        $studly = str_replace(' ', '', ucwords(str_replace('-', ' ', $this->slug)));
        return "App\\Http\\Controllers\\Admin\\{$studly}Controller";
    }

    /**
     * Get the view folder path for this menu.
     */
    public function getViewFolder(): string
    {
        return "admin.{$this->slug}";
    }
}
