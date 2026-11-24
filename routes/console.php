<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// The shared demo dataset is restored to its seeded baseline every hour.
// Disable with DEMO_RESET_HOURLY=false, or turn demo mode off entirely.
if (config('demo.enabled') && config('demo.reset_hourly')) {
    Schedule::command('demo:reset')->hourly()->withoutOverlapping();
}
