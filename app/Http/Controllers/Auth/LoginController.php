<?php

/**
 * ============================================================================
 * FILE PATH: app/Http/Controllers/Auth/LoginController.php
 * DESCRIPTION: Handles User / Super Admin Authentication (Login & Logout).
 * ============================================================================
 *
 * CONNECTED FILES:
 * - Routes File: routes/web.php (Routes: 'login', 'login.submit', 'logout')
 * - UI View (Login Page): resources/views/auth/login.blade.php
 * - Redirect Target (UI): resources/views/admin/dashboard.blade.php (via route 'admin.dashboard')
 * - User Model (Backend): app/Models/User.php
 * ============================================================================
 */

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    /**
     * PART 1: Display the Login Page UI.
     *
     * Route: GET /login (route name: 'login')
     * UI View Path: resources/views/auth/login.blade.php
     */
    public function showLoginForm(): View
    {
        $settings = SystemSetting::getAllMerged();

        return view('auth.login', [
            'supportEmail' => $settings['store_email'] ?? 'support@ism.com',
            'supportPhone' => $settings['store_phone'] ?? '+94 11 234 5678',
            'storeName' => $settings['store_name'] ?? 'ISM Retail & POS Flagship Store',
        ]);
    }

    /**
     * PART 2: Handle Login Form Submission (Backend Authentication).
     *
     * Route: POST /login (route name: 'login.submit')
     * Form Source: resources/views/auth/login.blade.php
     * Success Redirect: /admin/dashboard (app/Http/Controllers/Admin/DashboardController.php)
     */
    public function login(Request $request): RedirectResponse
    {
        // Step 1: Validate incoming email and password inputs from the login form
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Step 2: Check if "Keep me signed in" checkbox was ticked
        $remember = $request->boolean('remember');

        // Step 3: Attempt to authenticate against the 'users' database table (app/Models/User.php)
        if (Auth::attempt($credentials, $remember)) {
            // Regenerate session ID to prevent session fixation attacks
            $request->session()->regenerate();

            // Redirect authenticated Super Admin to the Admin Dashboard
            return redirect()->intended(route('admin.dashboard'));
        }

        // Step 4: If login fails, redirect back to resources/views/auth/login.blade.php with error message
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * PART 3: Handle User Logout.
     *
     * Route: POST /logout (route name: 'logout')
     * Triggered From: Sign Out button in resources/views/admin/dashboard.blade.php
     * Redirects To: GET /login (resources/views/auth/login.blade.php)
     */
    public function logout(Request $request): RedirectResponse
    {
        // Step 1: Log out user from the Auth guard
        Auth::logout();

        // Step 2: Invalidate current session and regenerate CSRF token
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Step 3: Redirect back to the Login UI with a status message
        return redirect()->route('login')->with('status', 'You have been signed out successfully.');
    }

    /**
     * PART 4: Handle Password Reset / Account Recovery Request to System Admin.
     *
     * Route: POST /forgot-password-request (route name: 'password.request.admin')
     * Triggered From: "Forgot password? / Contact Administrator" Modal in resources/views/auth/login.blade.php
     */
    public function requestPasswordReset(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_email' => ['required', 'email', 'max:150'],
            'staff_role' => ['required', 'string', 'max:60'],
            'reason_note' => ['nullable', 'string', 'max:300'],
        ]);

        $matchedUser = User::where('email', strtolower(trim($validated['staff_email'])))->first();
        $displayTarget = $matchedUser
            ? "{$matchedUser->name} ({$matchedUser->email})"
            : $validated['staff_email'];

        return redirect()
            ->route('login')
            ->with(
                'status',
                "Password reset request for {$displayTarget} has been sent to the System Administrator. An Admin can reset your password under User Management (/admin/users)."
            );
    }
}
