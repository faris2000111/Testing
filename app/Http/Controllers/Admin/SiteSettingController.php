<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SiteSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SiteSettingController extends Controller
{
    public function edit(): View
    {
        $setting = SiteSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'project_name' => config('app.name', 'My Project'),
                'site_name' => config('app.name', 'My Website'),
            ]
        );

        return view('admin.settings.edit', compact('setting'));
    }

    public function update(Request $request): RedirectResponse
    {
        $setting = SiteSetting::query()->firstOrCreate(
            ['id' => 1],
            [
                'project_name' => config('app.name', 'My Project'),
                'site_name' => config('app.name', 'My Website'),
            ]
        );

        $validated = $request->validate([
            'project_name' => ['required', 'string', 'max:255'],
            'site_name' => ['required', 'string', 'max:255'],
            'tagline' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:4096'],
            'logo_dark' => ['nullable', 'image', 'max:4096'],
            'favicon' => ['nullable', 'image', 'max:2048'],
            'apple_touch_icon' => ['nullable', 'image', 'max:4096'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'github_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
            'meta_keywords' => ['nullable', 'string'],
            'maintenance_mode' => ['nullable', 'boolean'],
            'footer_layout' => ['nullable', 'string', Rule::in(['classic', 'minimal', 'split'])],
            'navbar_layout' => ['nullable', 'string', Rule::in(['classic', 'minimal', 'branded'])],
        ]);

        // Handle file uploads
        foreach (['logo', 'logo_dark', 'favicon', 'apple_touch_icon'] as $field) {
            if ($request->hasFile($field)) {
                $newPath = $request->file($field)->store('site-settings', 'public');
                $setting->purgeMediaIfChanged($field, $newPath);
                $validated[$field] = $newPath;
            } else {
                unset($validated[$field]);
            }
        }

        $validated['maintenance_mode'] = $request->boolean('maintenance_mode');
        $validated['footer_layout'] = $validated['footer_layout'] ?? $setting->footer_layout ?? 'classic';
        $validated['navbar_layout'] = $validated['navbar_layout'] ?? $setting->navbar_layout ?? 'classic';

        $setting->update($validated);

        ActivityLog::record('updated', $setting, 'Memperbarui pengaturan website.');

        return redirect()->route('admin.settings.edit')->with('success', 'Pengaturan website berhasil diperbarui.');
    }
}
