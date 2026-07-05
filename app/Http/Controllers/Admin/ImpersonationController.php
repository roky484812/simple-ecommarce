<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationController extends Controller
{
    /**
     * Log in as the given customer, remembering the admin's own ID in the
     * session so they can return to their own account afterwards. Admins
     * can never impersonate another admin.
     */
    public function start(User $user): RedirectResponse
    {
        $admin = Auth::user();

        if ($user->isAdmin()) {
            abort(403, 'Admins cannot impersonate other admins.');
        }

        if ($user->is_blocked) {
            return redirect()
                ->route('admin.customers.show', $user)
                ->with('error', 'Cannot log in as a blocked customer.');
        }

        session(['impersonator_id' => $admin->id]);

        Log::info("Admin #{$admin->id} ({$admin->email}) started impersonating user #{$user->id} ({$user->email}).");

        Auth::login($user);

        return redirect()->route('home')->with('success', "You are now logged in as {$user->name}.");
    }

    /**
     * Stop impersonating and return to the original admin account.
     */
    public function stop(): RedirectResponse
    {
        $impersonatorId = session('impersonator_id');

        abort_unless($impersonatorId, 403);

        $admin = User::findOrFail($impersonatorId);
        $impersonated = Auth::user();

        session()->forget('impersonator_id');

        Log::info("Admin #{$admin->id} ({$admin->email}) stopped impersonating user #{$impersonated?->id} ({$impersonated?->email}).");

        Auth::login($admin);

        return redirect()->route('admin.customers.index')->with('success', 'You have returned to your admin account.');
    }
}
