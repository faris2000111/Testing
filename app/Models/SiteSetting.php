<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'maintenance_mode' => 'boolean',
        'maintenance_pages' => 'array',
        'github_widget_enabled' => 'boolean',
        'spotify_widget_enabled' => 'boolean',
        'youtube_live_auto_post_enabled' => 'boolean',
        'tiktok_live_auto_post_enabled' => 'boolean',
        'cookie_consent_enabled' => 'boolean',
        'hero_ab_testing_enabled' => 'boolean',
        'chatbot_enabled' => 'boolean',
    ];

    // ─── Accessor helpers for image URLs ───

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? Storage::disk('public')->url($this->logo) : null;
    }

    public function getLogoDarkUrlAttribute(): ?string
    {
        return $this->logo_dark ? Storage::disk('public')->url($this->logo_dark) : null;
    }

    public function getFaviconUrlAttribute(): ?string
    {
        return $this->favicon ? Storage::disk('public')->url($this->favicon) : null;
    }

    public function getAppleTouchIconUrlAttribute(): ?string
    {
        return $this->apple_touch_icon ? Storage::disk('public')->url($this->apple_touch_icon) : null;
    }

    // ─── Maintenance areas ───

    public static function maintenanceAreas(): array
    {
        return [
            'blog' => 'Blog',
            'portfolio' => 'Portfolio',
            'contact' => 'Kontak',
            'guestbook' => 'Guestbook',
        ];
    }

    // ─── Helper: purge old media when replaced ───

    public function purgeMediaIfChanged(string $field, string $newPath): void
    {
        $old = $this->getOriginal($field);
        if ($old && $old !== $newPath && Storage::disk('public')->exists($old)) {
            Storage::disk('public')->delete($old);
        }
    }
}
