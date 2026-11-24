<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Auth;

use App\Modules\Users\Models\Role;
use Tests\TestCase;

class PermissionsTest extends TestCase
{
    public function test_admin_can_access_administration_and_finance_routes(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/dashboard')->assertOk();
        $this->actingAs($admin)->get('/users')->assertOk();
        $this->actingAs($admin)->get('/roles')->assertOk();
        $this->actingAs($admin)->get('/settings')->assertOk();
    }

    public function test_sales_user_cannot_access_user_administration(): void
    {
        $sales = $this->makeUser('sales');

        $this->actingAs($sales)->get('/users')->assertForbidden();
        $this->actingAs($sales)->get('/settings')->assertForbidden();
        $this->actingAs($sales)->get('/invoices/create')->assertOk();
        $this->actingAs($sales)->get('/customers/create')->assertOk();
    }

    public function test_warehouse_user_can_manage_stock_but_not_create_invoices(): void
    {
        $warehouse = $this->makeUser('warehouse');

        $this->actingAs($warehouse)->get('/inventory/create')->assertOk();
        $this->actingAs($warehouse)->get('/invoices/create')->assertForbidden();
        $this->actingAs($warehouse)->get('/expenses')->assertForbidden();
    }

    public function test_accountant_can_manage_expenses_but_not_users(): void
    {
        $accountant = $this->makeUser('accountant');

        $this->actingAs($accountant)->get('/expenses/create')->assertOk();
        $this->actingAs($accountant)->get('/payments/create')->assertOk();
        $this->actingAs($accountant)->get('/users')->assertForbidden();
    }

    public function test_admin_can_edit_permission_matrix_and_effect_is_immediate(): void
    {
        $admin = $this->makeUser('admin');
        $salesRole = Role::query()->where('name', 'sales')->firstOrFail();
        $salesUser = $this->makeUser('sales');

        $this->actingAs($salesUser)->get('/users')->assertForbidden();

        $permissions = array_values(array_unique(array_merge($salesRole->permissions, ['users.view'])));
        $this->actingAs($admin)->put('/roles/'.$salesRole->id, ['permissions' => $permissions])->assertRedirect('/roles');

        $this->assertTrue($salesRole->fresh()->hasPermission('users.view'));
        $this->actingAs($salesUser->fresh())->get('/users')->assertOk();
        $this->assertDatabaseHas('activity_logs', ['action' => 'permissions_updated', 'module' => 'roles']);
    }

    public function test_admin_role_permissions_cannot_be_weakened(): void
    {
        $admin = $this->makeUser('admin');
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)->put('/roles/'.$adminRole->id, ['permissions' => ['dashboard.view']])
            ->assertSessionHasErrors('permissions');

        $this->assertCount(count(Role::allPermissionKeys()), $adminRole->fresh()->permissions);
    }

    public function test_guest_cannot_access_business_routes(): void
    {
        $this->get('/customers')->assertRedirect('/login');
        $this->get('/reports')->assertRedirect('/login');
        $this->get('/inventory')->assertRedirect('/login');
    }
}
