<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ImpersonateController extends Controller
{
    /**
     * Start impersonating a user.
     * Only superadmin can impersonate.
     */
    public function start(User $user): RedirectResponse
    {
        $admin = auth()->user();

        // Only superadmin can impersonate
        if (! $admin->isSuperAdmin()) {
            abort(403, 'Hanya superadmin yang bisa impersonate.');
        }

        // Can't impersonate yourself
        if ($admin->id === $user->id) {
            return back()->with('error', 'Tidak bisa impersonate diri sendiri.');
        }

        // Store original admin ID in session
        session()->put('impersonate_admin_id', $admin->id);

        ActivityLog::record('impersonate', $user, "Impersonate sebagai: {$user->name}");

        // Login as the target user
        auth()->login($user);

        return redirect()->route('admin.dashboard')->with('success', "Sekarang login sebagai {$user->name}.");
    }

    /**
     * Stop impersonating and return to admin account.
     */
    public function stop(): RedirectResponse
    {
        $adminId = session()->get('impersonate_admin_id');

        if (! $adminId) {
            return redirect()->route('admin.dashboard')->with('error', 'Tidak sedang impersonate.');
        }

        $admin = User::find($adminId);

        if (! $admin) {
            session()->forget('impersonate_admin_id');
            return redirect()->route('admin.dashboard')->with('error', 'Admin tidak ditemukan.');
        }

        // Switch back to admin
        auth()->login($admin);
        session()->forget('impersonate_admin_id');

        return redirect()->route('admin.dashboard')->with('success', 'Kembali ke akun admin.');
    }
}
