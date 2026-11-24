<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Database\Seeders;

use App\Modules\Users\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $all = Role::allPermissionKeys();

        $roles = [
            'admin' => ['name_ar' => 'مدير النظام', 'name_en' => 'Administrator', 'permissions' => $all, 'is_system' => true],
            'accountant' => ['name_ar' => 'محاسب', 'name_en' => 'Accountant', 'permissions' => [
                'dashboard.view', 'customers.view', 'suppliers.view', 'products.view',
                'quotations.view', 'invoices.view', 'payments.view', 'payments.manage',
                'expenses.view', 'expenses.manage', 'reports.view', 'activity.view',
            ], 'is_system' => true],
            'sales' => ['name_ar' => 'مبيعات', 'name_en' => 'Sales', 'permissions' => [
                'dashboard.view', 'customers.view', 'customers.manage', 'suppliers.view',
                'products.view', 'quotations.view', 'quotations.manage', 'invoices.view',
                'invoices.manage', 'payments.view', 'payments.manage', 'reports.view',
            ], 'is_system' => true],
            'warehouse' => ['name_ar' => 'مخازن', 'name_en' => 'Warehouse', 'permissions' => [
                'dashboard.view', 'suppliers.view', 'products.view', 'inventory.view', 'inventory.manage',
            ], 'is_system' => true],
        ];

        foreach ($roles as $name => $data) {
            Role::updateOrCreate(['name' => $name], $data);
        }
    }
}
