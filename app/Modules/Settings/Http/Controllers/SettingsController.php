<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Modules\Settings\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\CompanySetting;
use App\Modules\Settings\Http\Requests\SettingsRequest;
use App\Modules\Settings\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $service) {}

    public function show(): View
    {
        $this->authorizePermission('settings.manage');
        $settings = CompanySetting::current();

        return view('settings.index', compact('settings'));
    }

    public function update(SettingsRequest $request): RedirectResponse
    {
        $this->authorizePermission('settings.manage');
        $this->service->update(CompanySetting::current(), $request->validated(), $request->file('logo'));

        return redirect()->route('settings.index')->with('success', __('messages.settings_saved'));
    }

    public function removeLogo(): RedirectResponse
    {
        $this->authorizePermission('settings.manage');
        $this->service->removeLogo(CompanySetting::current());

        return redirect()->route('settings.index')->with('success', __('messages.settings_saved'));
    }
}
