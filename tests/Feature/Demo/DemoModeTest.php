<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Demo;

use App\Models\User;
use App\Modules\Catalog\Models\Product;
use App\Modules\Core\Models\CompanySetting;
use App\Modules\CRM\Models\Customer;
use App\Modules\Expenses\Models\Expense;
use App\Modules\Sales\Models\Invoice;
use App\Modules\Sales\Models\Payment;
use App\Modules\Sales\Models\Quotation;
use App\Modules\Users\Models\Role;
use Database\Seeders\DemoSeeder;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    private function seedDemo(): void
    {
        $this->seed(DemoSeeder::class);
    }

    public function test_login_page_offers_demo_entry_points_when_enabled(): void
    {
        $this->seedDemo();

        $this->get('/login')
            ->assertOk()
            ->assertSee(__('auth.demo_try_title'), false)
            ->assertSee(route('demo.login'), false);
    }

    public function test_guest_can_enter_the_demo_as_admin_without_a_password(): void
    {
        $this->seedDemo();

        $this->post('/demo/login', ['role' => 'admin'])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'admin@businessflow.test')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertTrue($user->is_demo);
        $this->assertDatabaseHas('activity_logs', ['action' => 'login', 'user_id' => $user->id]);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_demo_entry_is_not_available_when_demo_mode_is_disabled(): void
    {
        config(['demo.enabled' => false]);
        $this->seedDemo();

        $this->post('/demo/login', ['role' => 'admin'])->assertNotFound();
        $this->get('/login')->assertOk()->assertDontSee(route('demo.login'), false);
    }

    public function test_demo_login_rejects_an_unknown_role(): void
    {
        $this->seedDemo();

        $this->post('/demo/login', ['role' => 'owner'])->assertSessionHasErrors('role');
        $this->assertGuest();
    }

    public function test_demo_banner_is_shown_in_the_workspace(): void
    {
        $this->seedDemo();
        $admin = User::query()->where('email', 'admin@businessflow.test')->firstOrFail();

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee(__('common.demo_mode_banner'), false);
    }

    public function test_demo_account_email_and_password_are_locked(): void
    {
        $this->seedDemo();
        $admin = User::query()->where('email', 'admin@businessflow.test')->firstOrFail();
        $accountant = User::query()->where('email', 'accountant@businessflow.test')->firstOrFail();
        $role = Role::query()->where('name', 'accountant')->firstOrFail();

        $this->actingAs($admin)->put('/users/'.$accountant->id, [
            'name' => 'Changed Name',
            'email' => 'changed@businessflow.test',
            'role_id' => $role->id,
            'locale' => 'ar',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
        ])->assertSessionHasErrors(['email', 'password']);

        $this->assertSame('accountant@businessflow.test', $accountant->fresh()->email);
    }

    public function test_demo_account_cannot_be_deleted(): void
    {
        $this->seedDemo();
        $admin = User::query()->where('email', 'admin@businessflow.test')->firstOrFail();
        $accountant = User::query()->where('email', 'accountant@businessflow.test')->firstOrFail();

        $this->actingAs($admin)->delete('/users/'.$accountant->id)->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('users', ['id' => $accountant->id]);
    }

    public function test_demo_user_cannot_create_another_administrator(): void
    {
        $this->seedDemo();
        $admin = User::query()->where('email', 'admin@businessflow.test')->firstOrFail();
        $adminRole = Role::query()->where('name', 'admin')->firstOrFail();

        $this->actingAs($admin)->post('/users', [
            'name' => 'Sneaky Admin',
            'email' => 'sneaky@businessflow.test',
            'role_id' => $adminRole->id,
            'locale' => 'ar',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
        ])->assertSessionHasErrors('role_id');

        $this->assertDatabaseMissing('users', ['email' => 'sneaky@businessflow.test']);
    }

    public function test_demo_reset_command_no_ops_when_demo_mode_is_disabled(): void
    {
        config(['demo.enabled' => false]);

        $this->artisan('demo:reset')->assertExitCode(0);
    }

    public function test_demo_company_and_rich_dataset_are_seeded(): void
    {
        $this->seedDemo();

        $settings = CompanySetting::current();
        $this->assertSame('النيل للتوريدات المكتبية', $settings->company_name_ar);
        $this->assertSame('Nile Office Supply', $settings->company_name_en);

        $this->assertGreaterThanOrEqual(20, Customer::count());
        $this->assertGreaterThanOrEqual(15, Product::count());
        $this->assertGreaterThanOrEqual(20, Quotation::count());
        $this->assertGreaterThanOrEqual(40, Invoice::count());
        $this->assertGreaterThanOrEqual(20, Payment::count());
        $this->assertGreaterThanOrEqual(30, Expense::count());

        $this->assertDatabaseHas('invoices', ['status' => 'paid']);
        $this->assertDatabaseHas('invoices', ['status' => 'partially_paid']);
        $this->assertDatabaseHas('invoices', ['status' => 'cancelled']);
    }

    public function test_login_places_demo_entry_above_the_form_on_phones(): void
    {
        $this->seedDemo();

        $html = $this->get('/login')->getContent();

        $this->assertStringContainsString('order-2 sm:order-1', $html);
        $this->assertStringContainsString('order-1 mb-6 sm:order-2', $html);
    }
}
