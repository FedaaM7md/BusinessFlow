<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests;

use App\Models\User;
use App\Modules\Catalog\Models\Product;
use App\Modules\CRM\Models\Customer;
use App\Modules\Inventory\Models\Warehouse;
use App\Modules\Users\Models\Role;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Str;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([RoleSeeder::class, SettingSeeder::class]);
    }

    protected function makeUser(string $role = 'admin', array $attributes = []): User
    {
        $roleModel = Role::query()->where('name', $role)->firstOrFail();

        return User::factory()->create(array_merge([
            'role_id' => $roleModel->id,
            'is_active' => true,
            'locale' => 'ar',
        ], $attributes));
    }

    protected function makeWarehouse(array $attributes = []): Warehouse
    {
        return Warehouse::create(array_merge([
            'code' => 'WH-'.Str::upper(Str::random(5)),
            'name' => 'Test Warehouse',
            'location' => 'Cairo',
            'is_default' => true,
            'is_active' => true,
        ], $attributes));
    }

    protected function makeCustomer(array $attributes = []): Customer
    {
        return Customer::create(array_merge([
            'code' => 'CUS-T'.Str::upper(Str::random(5)),
            'type' => 'company',
            'name' => 'Test Customer',
            'email' => 'customer@example.test',
            'phone' => '+20 100 000 0000',
            'is_active' => true,
        ], $attributes));
    }

    protected function makeProduct(array $attributes = []): Product
    {
        return Product::create(array_merge([
            'sku' => 'SKU-'.Str::upper(Str::random(6)),
            'name' => 'Test Product',
            'type' => 'product',
            'unit' => 'piece',
            'price_minor' => 100000,
            'cost_minor' => 60000,
            'tax_rate' => 1400,
            'is_tracked' => true,
            'low_stock_threshold' => 5,
            'is_active' => true,
        ], $attributes));
    }
}
