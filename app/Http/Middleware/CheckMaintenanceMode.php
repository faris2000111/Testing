<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Block non-superadmin users when maintenance mode is active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $setting = SiteSetting::first();

        if ($setting && $setting->maintenance_mode) {
            $user = $request->user();

            // Superadmin bypasses maintenance mode
            if ($user && $user->isSuperAdmin()) {
                return $next($request);
            }

            // Allow login/logout routes so users can still authenticate
            if ($request->routeIs('login') || $request->routeIs('logout')) {
                return $next($request);
            }

            return response()->view('errors.503', [], 503);
        }

        return $next($request);
    }
}
