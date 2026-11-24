<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', Rule::in(['ar', 'en'])],
        ]);

        $request->session()->put('locale', $data['locale']);

        if ($request->user()) {
            $request->user()->update(['locale' => $data['locale']]);
        }

        app()->setLocale($data['locale']);

        return back();
    }
}
