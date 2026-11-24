<?php

namespace Tests\Feature;

use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    public function test_public_pages_render_in_the_default_locale(): void
    {
        foreach (['/about', '/privacy', '/terms'] as $path) {
            $this->get($path)->assertOk()->assertSee('BusinessFlow', false);
        }
    }

    public function test_footer_credit_and_copyright_follow_the_addendum(): void
    {
        $response = $this->get('/about')->assertOk();

        $response->assertSee('https://fedaamohamed.com', false);
        $response->assertSee('تصميم وتطوير: فداء محمد', false);
        $response->assertSee('© 2026 BusinessFlow. جميع الحقوق محفوظة.', false);
        $response->assertSee('نسخة عرض — البيانات افتراضية', false);
        $response->assertDontSee('من فداء', false);
    }

    public function test_public_pages_expose_open_graph_metadata(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('property="og:title"', false)
            ->assertSee('property="og:description"', false)
            ->assertSee('images/og.png', false);
    }

    public function test_english_credit_is_rendered_when_locale_is_english(): void
    {
        $this->get('/about');
        session(['locale' => 'en']);

        $this->get('/about')
            ->assertOk()
            ->assertSee('Designed & built by Fedaa Mohamed')
            ->assertSee('© 2026 BusinessFlow. All rights reserved.', false);
    }
}
