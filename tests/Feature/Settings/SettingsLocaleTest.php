<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Settings;

use App\Models\User;
use App\Modules\Core\Models\CompanySetting;
use App\Modules\Sales\Models\Quotation;
use App\Modules\Users\Models\Role;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SettingsLocaleTest extends TestCase
{
    public function test_user_can_switch_between_arabic_rtl_and_english_ltr(): void
    {
        $admin = $this->makeUser('admin', ['locale' => 'ar']);

        $this->actingAs($admin)->post('/locale', ['locale' => 'en'])->assertRedirect();
        $this->assertSame('en', $admin->fresh()->locale);

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('dir="ltr"', false)
            ->assertSee('Dashboard', false);

        $this->actingAs($admin)->post('/locale', ['locale' => 'ar'])->assertRedirect();
        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('dir="rtl"', false);
    }

    public function test_locale_rejects_invalid_value(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/locale', ['locale' => 'fr'])->assertSessionHasErrors('locale');
    }

    public function test_settings_can_be_updated_with_logo_and_prefixes_affect_numbering(): void
    {
        Storage::fake('public');
        $admin = $this->makeUser('admin');
        $settings = CompanySetting::current();

        $payload = [
            'company_name_ar' => 'شركة اختبار',
            'company_name_en' => 'Test Company',
            'address_ar' => 'القاهرة',
            'address_en' => 'Cairo',
            'phone' => '+20 100 000 0000',
            'email' => 'hello@example.test',
            'tax_number' => 'TAX-1',
            'currency_code' => 'egp',
            'currency_symbol_ar' => 'ج.م',
            'currency_symbol_en' => 'EGP',
            'default_tax_rate' => '15',
            'quotation_prefix' => 'quo',
            'invoice_prefix' => 'inv',
            'payment_prefix' => 'pay',
            'expense_prefix' => 'exp',
            'default_locale' => 'en',
            'logo' => UploadedFile::fake()->image('logo.png', 120, 60),
        ];

        $this->actingAs($admin)->put('/settings', $payload)->assertRedirect('/settings');

        $settings->refresh();
        $this->assertSame('Test Company', $settings->company_name_en);
        $this->assertSame('EGP', $settings->currency_code);
        $this->assertSame(1500, $settings->default_tax_rate);
        $this->assertSame('QUO', $settings->quotation_prefix);
        $this->assertNotNull($settings->logo_path);
        Storage::disk('public')->assertExists($settings->logo_path);

        $customer = $this->makeCustomer();
        $this->actingAs($admin)->post('/quotations', [
            'customer_id' => $customer->id,
            'issue_date' => now()->format('Y-m-d'),
            'lines' => [['description' => 'Prefix test', 'quantity' => '1', 'unit_price' => '10.00', 'tax_rate' => '15']],
        ]);
        $this->assertSame('QUO-'.now()->year.'-0001', Quotation::query()->latest('id')->firstOrFail()->number);
    }

    public function test_settings_validation_and_logo_type_validation(): void
    {
        Storage::fake('public');
        $admin = $this->makeUser('admin');
        $settings = CompanySetting::current();

        $this->actingAs($admin)->put('/settings', [
            'company_name_ar' => '',
            'company_name_en' => '',
            'currency_code' => 'TOOLONG',
            'default_tax_rate' => '101',
            'quotation_prefix' => 'bad prefix!',
            'logo' => UploadedFile::fake()->create('malware.pdf', 50, 'application/pdf'),
        ])->assertSessionHasErrors([
            'company_name_ar', 'company_name_en', 'currency_code', 'default_tax_rate',
            'quotation_prefix', 'logo', 'invoice_prefix', 'payment_prefix', 'expense_prefix',
        ]);

        $this->assertSame($settings->company_name_en, $settings->fresh()->company_name_en);
    }

    public function test_admin_can_create_update_and_validate_users(): void
    {
        $admin = $this->makeUser('admin');
        $accountantRole = Role::query()->where('name', 'accountant')->firstOrFail();

        $this->actingAs($admin)->post('/users', [
            'name' => 'New Accountant',
            'email' => 'new.accountant@example.test',
            'role_id' => $accountantRole->id,
            'locale' => 'en',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'is_active' => '1',
        ])->assertRedirect('/users');

        $user = User::query()->where('email', 'new.accountant@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('Password123!', $user->password));
        $this->assertSame('en', $user->locale);

        $this->actingAs($admin)->post('/users', [
            'name' => '',
            'email' => 'not-an-email',
            'role_id' => 999,
            'locale' => 'fr',
            'password' => 'short',
            'password_confirmation' => 'different',
            'is_active' => '1',
        ])->assertSessionHasErrors(['name', 'email', 'role_id', 'locale', 'password']);

        $this->actingAs($admin)->put('/users/'.$user->id, [
            'name' => 'Updated Accountant',
            'email' => 'new.accountant@example.test',
            'role_id' => $accountantRole->id,
            'locale' => 'ar',
            'password' => '',
            'password_confirmation' => '',
            'is_active' => '0',
        ])->assertRedirect('/users');
        $this->assertSame('Updated Accountant', $user->fresh()->name);
        $this->assertFalse($user->fresh()->is_active);

        $this->actingAs($admin)->delete('/users/'.$user->id)->assertRedirect('/users');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_admin_cannot_deactivate_or_delete_their_own_account(): void
    {
        $admin = $this->makeUser('admin');
        $role = $admin->role;

        $this->actingAs($admin)->put('/users/'.$admin->id, [
            'name' => $admin->name,
            'email' => $admin->email,
            'role_id' => $role->id,
            'locale' => 'ar',
            'is_active' => '0',
        ])->assertSessionHasErrors('is_active');

        $this->assertTrue($admin->fresh()->is_active);

        $this->actingAs($admin)->delete('/users/'.$admin->id)->assertSessionHasErrors('delete');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
