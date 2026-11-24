<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ResetDemoWorkspace extends Command
{
    protected $signature = 'demo:reset';

    protected $description = 'Reset the shared demo workspace to its clearly labelled seeded baseline';

    public function handle(): int
    {
        if (! demo_enabled()) {
            $this->warn('Demo mode is disabled; there is nothing to reset.');

            return self::SUCCESS;
        }

        $this->info('Resetting the demo workspace…');
        $this->call('migrate:fresh', ['--seed' => true, '--force' => true]);
        $this->info('Demo workspace restored to its seeded baseline.');

        return self::SUCCESS;
    }
}
