<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Auth\Http\Requests\LoginRequest;
use App\Modules\Core\Services\ActivityLogger;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Users\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private readonly ActivityLogger $activity) {}

    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $demoRoles = collect();
        $preview = null;

        if (demo_enabled()) {
            try {
                $demoRoles = Role::query()
                    ->whereHas('users', fn ($query) => $query->where('is_demo', true)->where('is_active', true))
                    ->orderBy('name')
                    ->get();

                $preview = Invoice::query()
                    ->with(['lines.product', 'customer'])
                    ->whereIn('status', ['issued', 'partially_paid', 'paid'])
                    ->latest('issue_date')
                    ->latest('id')
                    ->first();
            } catch (\Throwable) {
                $demoRoles = collect();
                $preview = null;
            }
        }

        return view('auth.login', [
            'demoRoles' => $demoRoles instanceof Collection ? $demoRoles : collect(),
            'preview' => $preview,
        ]);
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $key = 'login:'.Str::transliterate(Str::lower($request->validated('email'))).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        $credentials = [
            'email' => $request->validated('email'),
            'password' => $request->validated('password'),
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        $this->activity->log('auth', 'login', 'User '.$user->email.' logged in', $user);

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Enter the shared demo workspace as a seeded demo user without a password.
     * Disabled (404) when DEMO_MODE is false.
     */
    public function demoLogin(Request $request): RedirectResponse
    {
        abort_unless(demo_enabled(), 404);

        $data = $request->validate([
            'role' => ['required', 'string', 'in:admin,accountant,sales,warehouse'],
        ]);

        $key = 'demo-login:'.$request->ip();
        $maxAttempts = (int) config('demo.login_attempts_per_minute', 10);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            throw ValidationException::withMessages([
                'role' => __('auth.throttle', ['seconds' => RateLimiter::availableIn($key)]),
            ]);
        }

        RateLimiter::hit($key, 60);

        $user = User::query()
            ->where('is_demo', true)
            ->where('is_active', true)
            ->whereHas('role', fn ($query) => $query->where('name', $data['role']))
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'role' => __('auth.failed'),
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $user->forceFill(['last_login_at' => now()])->save();
        $this->activity->log('auth', 'login', 'Demo login as '.$user->email, $user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user) {
            $this->activity->log('auth', 'logout', 'User '.$user->email.' logged out', $user);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
