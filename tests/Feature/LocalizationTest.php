<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Lang;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_every_literal_translation_key_exists_in_arabic_and_english(): void
    {
        $files = array_merge(
            File::allFiles(resource_path('views')),
            File::allFiles(app_path()),
        );

        $keys = [];

        foreach ($files as $file) {
            if (! in_array($file->getExtension(), ['php'], true)) {
                continue;
            }

            preg_match_all("/__\\('([^']+)'/u", $file->getContents(), $matches);
            foreach ($matches[1] as $key) {
                if (! str_ends_with($key, '.')) {
                    $keys[$key] = true;
                }
            }
        }

        foreach (array_keys($keys) as $key) {
            $this->assertTrue(Lang::has($key, 'en'), "Missing English translation key [{$key}].");
            $this->assertTrue(Lang::has($key, 'ar'), "Missing Arabic translation key [{$key}].");
        }
    }

    public function test_permission_status_and_module_labels_exist_in_both_locales(): void
    {
        $keys = ['permissions.groups.dashboard', 'permissions.groups.settings'];
        foreach (config('permissions.groups') as $group) {
            $keys[] = $group['label'];
            foreach ($group['permissions'] as $permission) {
                $keys[] = 'permissions.items.'.$permission;
            }
        }

        foreach (['draft', 'sent', 'accepted', 'rejected', 'expired', 'issued', 'partially_paid', 'paid', 'cancelled', 'in', 'out', 'adjustment', 'transfer', 'cash', 'bank_transfer', 'card', 'cheque', 'other', 'overdue'] as $status) {
            $keys[] = 'statuses.'.$status;
        }

        foreach (['auth', 'crm', 'suppliers', 'catalog', 'inventory', 'sales', 'payments', 'expenses', 'users', 'roles', 'settings'] as $module) {
            $keys[] = 'activity.modules.'.$module;
        }

        foreach (['admin', 'accountant', 'sales', 'warehouse'] as $role) {
            $keys[] = 'roles.'.$role;
        }

        foreach ($keys as $key) {
            $this->assertTrue(Lang::has($key, 'en'), "Missing English label [{$key}].");
            $this->assertTrue(Lang::has($key, 'ar'), "Missing Arabic label [{$key}].");
        }
    }
}
