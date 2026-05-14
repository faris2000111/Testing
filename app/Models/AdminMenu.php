<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     */
    public static function getMenuTree(): array
    {
        $menus = self::active()
            ->roots()
            ->with(['children' => fn ($q) => $q->active()->orderBy('order')])
            ->orderBy('section')
            ->orderBy('order')
            ->get();

        $grouped = [];
        foreach ($menus as $menu) {
            $section = $menu->section ?: 'Menu';
            if (! isset($grouped[$section])) {
                $grouped[$section] = [];
            }
            $grouped[$section][] = $menu;
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
