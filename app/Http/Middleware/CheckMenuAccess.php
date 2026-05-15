<?php

namespace App\Http\Middleware;

use App\Models\AdminMenu;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMenuAccess
{
    /**
     * Check if the authenticated user has access to the current route's menu.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->role) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Superadmin bypasses all checks
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Try to match current route to a menu
        $routeName = $request->route()?->getName();

        if ($routeName) {
            // Find menu by route_name match
            $menu = AdminMenu::where('route_name', $routeName)->first();

            // Also try matching by slug pattern (admin.{slug}.*)
            if (! $menu) {
                $parts = explode('.', $routeName);
                // Pattern: admin.{slug}.action
                if (count($parts) >= 3 && $parts[0] === 'admin') {
                    $slug = $parts[1];
                    $menu = AdminMenu::where('slug', $slug)->first();
                }
            }

            if ($menu && ! $user->hasMenuAccess($menu)) {
                abort(403, 'Anda tidak memiliki akses ke halaman ini.');
            }
        }

        return $next($request);
    }
}
