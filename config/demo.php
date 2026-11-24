<?php

return [
    /*
    | Demo mode is a first-class part of this application. When enabled, the
    | login screen offers one-click demo entry and a persistent banner warns
    | that the shared dataset resets. Set DEMO_MODE=false to hide every demo
    | entry point; their routes then return 404.
    */
    'enabled' => (bool) env('DEMO_MODE', true),

    // The scheduler runs `demo:reset` hourly when this is true.
    'reset_hourly' => (bool) env('DEMO_RESET_HOURLY', true),

    // Demo login is rate limited separately from password login.
    'login_attempts_per_minute' => (int) env('DEMO_LOGIN_ATTEMPTS', 10),
];
