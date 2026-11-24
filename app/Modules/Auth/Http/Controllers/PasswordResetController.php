<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    public function showRequestForm(): View
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email:rfc', 'max:255']]);

        $user = User::query()->where('email', strtolower((string) $request->input('email')))->first();

        // Send only for real, active, non-demo accounts. The response is always
        // the same generic message so the endpoint cannot enumerate accounts.
        if ($user && ! $user->is_demo && $user->is_active) {
            Password::broker()->sendResetLink(['email' => $user->email]);
        }

        return back()->with('success', __('auth.reset_sent'));
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::min(10)->mixedCase()->numbers()],
        ]);

        $user = User::query()->where('email', strtolower((string) $request->input('email')))->first();

        if ($user?->is_demo) {
            throw ValidationException::withMessages(['email' => __('auth.reset_invalid')]);
        }

        $status = Password::broker()->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => __('auth.reset_invalid')]);
        }

        $request->session()->regenerate();

        return redirect()->route('login')->with('success', __('auth.reset_done'));
    }
}
