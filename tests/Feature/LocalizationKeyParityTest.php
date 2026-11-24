<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature;

use Tests\TestCase;

class LocalizationKeyParityTest extends TestCase
{
    public function test_arabic_and_english_translation_key_sets_are_identical(): void
    {
        $en = $this->flatten($this->loadLocale('en'));
        $ar = $this->flatten($this->loadLocale('ar'));

        $this->assertSame($en, $ar);
    }

    public function test_brand_name_is_standalone_and_contains_no_owner_prefix(): void
    {
        $this->assertSame('BusinessFlow', __('common.app_name', [], 'en'));
        $this->assertSame('BusinessFlow', __('common.app_name', [], 'ar'));

        foreach (['en', 'ar'] as $locale) {
            $name = (string) __('common.app_name', [], $locale);
            $this->assertStringNotContainsString('Fedaa', $name);
            $this->assertStringNotContainsString('فداء', $name);
        }
    }

    public function test_login_page_title_uses_the_product_brand_only(): void
    {
        $response = $this->get('/login')->assertOk();

        $response->assertSee('<title>', false);
        $response->assertSee(__('common.app_name'), false);
        $response->assertDontSee('Fedaa BusinessFlow', false);
        $response->assertDontSee('فداء بيزنس فلو', false);
    }

    private function loadLocale(string $locale): array
    {
        $data = [];

        foreach (glob(lang_path($locale).'/*.php') as $file) {
            $data[basename($file, '.php')] = require $file;
        }

        return $data;
    }

    /** @return array<int, string> */
    private function flatten(array $array, string $prefix = ''): array
    {
        $keys = [];

        foreach ($array as $key => $value) {
            $full = $prefix === '' ? (string) $key : $prefix.'.'.$key;

            if (is_array($value)) {
                $keys = array_merge($keys, $this->flatten($value, $full));
            } else {
                $keys[] = $full;
            }
        }

        sort($keys);

        return $keys;
    }
}
