<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     *
     * Post-login routing is role-aware:
     *   - administrators -> /admin/dashboard
     *   - regular users   -> / (home)
     *
     * The previous behaviour sent every user to `route('dashboard')`, which is
     * the admin-only `/admin/dashboard` endpoint. Non-admin users therefore
     * landed on a 403 page immediately after logging in.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended($this->homeFor($request->user()));
    }

    /**
     * Resolve the landing page for an authenticated user.
     */
    private function homeFor(?\App\Models\User $user): string
    {
        return ($user && $user->is_admin)
            ? route('dashboard', absolute: false)
            : route('home', absolute: false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
