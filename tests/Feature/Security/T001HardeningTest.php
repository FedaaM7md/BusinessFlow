<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Security;

use App\Modules\Users\Models\Role;
use Tests\TestCase;

class T001HardeningTest extends TestCase
{
    public function test_security_headers_are_present_on_public_responses(): void
    {
        $response = $this->get('/login');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Cross-Origin-Opener-Policy', 'same-origin');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
        $this->assertNotEmpty($response->headers->get('Permissions-Policy'));
    }

    public function test_content_security_policy_is_same_origin_for_scripts(): void
    {
        $csp = (string) $this->get('/login')->headers->get('Content-Security-Policy');

        $directives = collect(explode(';', $csp))
            ->map(fn ($directive) => trim($directive))
            ->filter()
            ->keyBy(fn ($directive) => explode(' ', $directive)[0]);

        $this->assertArrayHasKey('script-src', $directives->all());
        $this->assertStringContainsString("'self'", $directives['script-src']);
        $this->assertStringNotContainsString("'unsafe-inline'", $directives['script-src']);
        $this->assertStringNotContainsString("'unsafe-eval'", $directives['script-src']);
        $this->assertStringContainsString("'self'", $directives['style-src']);
        $this->assertStringNotContainsString("'unsafe-inline'", $directives['style-src']);
        $this->assertStringContainsString("'self'", $directives['default-src']);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
    }

    public function test_dashboard_renders_without_inline_style_attributes(): void
    {
        $admin = $this->makeUser('admin');

        $html = $this->actingAs($admin)->get('/dashboard')->assertOk()->getContent();

        $this->assertStringNotContainsString('style="', $html);
    }

    public function test_hsts_is_sent_when_the_application_url_is_https(): void
    {
        config(['app.url' => 'https://businessflow.test']);

        $this->get('/login')->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
    }

    public function test_weak_passwords_are_rejected_when_creating_users(): void
    {
        $admin = $this->makeUser('admin');
        $accountant = Role::query()->where('name', 'accountant')->firstOrFail();

        $this->actingAs($admin)->post('/users', [
            'name' => 'Weak Password',
            'email' => 'weak@example.test',
            'role_id' => $accountant->id,
            'locale' => 'ar',
            'password' => 'password',
            'password_confirmation' => 'password',
            'is_active' => '1',
        ])->assertSessionHasErrors('password');
    }

    public function test_date_fields_use_a_consistent_locale_format(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/invoices/create')
            ->assertOk()
            ->assertDontSee('type="date"', false)
            ->assertSee('YYYY-MM-DD', false);
    }

    public function test_list_pages_render_mobile_card_markup(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/invoices')
            ->assertOk()
            ->assertSee('md:hidden', false)
            ->assertSee('hidden md:block', false);
    }

    public function test_filter_panels_render_as_a_mobile_bottom_sheet(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->get('/invoices')
            ->assertOk()
            ->assertSee('fixed inset-x-0 bottom-0', false)
            ->assertSee('md:hidden', false);
    }

    public function test_csv_exports_neutralise_formula_injection(): void
    {
        $admin = $this->makeUser('admin');
        $this->makeProduct(['name' => '=cmd|calc', 'is_tracked' => true]);

        $response = $this->actingAs($admin)->get('/reports/export/stock');
        $response->assertOk();

        $content = $response->streamedContent();
        $this->assertStringContainsString("'=cmd|calc", $content);
        $this->assertStringNotContainsString("\n=cmd|calc", $content);
    }
}
