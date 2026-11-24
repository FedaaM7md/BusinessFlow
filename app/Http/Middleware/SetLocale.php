<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Http\Middleware;

use App\Modules\Core\Models\CompanySetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->user()?->locale ?: $request->session()->get('locale');

        if (! $locale) {
            try {
                $locale = CompanySetting::query()->value('default_locale');
            } catch (\Throwable) {
                $locale = null;
            }
        }

        if (! in_array($locale, ['ar', 'en'], true)) {
            $locale = config('app.locale', 'ar');
        }

        app()->setLocale($locale);
        $request->session()->put('locale', $locale);

        return $next($request);
    }
}
