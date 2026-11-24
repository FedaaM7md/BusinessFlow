<?php

/* BusinessFlow — Copyright (c) 2026 Fedaa Mohamed. All rights reserved. */

namespace Tests\Feature\Auth;

use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    public function test_login_screen_is_available_in_arabic_by_default(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('dir="rtl"', false)
            ->assertSee('تسجيل الدخول', false);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_and_session_is_regenerated(): void
    {
        $user = $this->makeUser('admin', ['email' => 'admin@example.test', 'password' => 'secret1234']);

        $this->get('/login');
        $oldSession = session()->getId();

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'secret1234',
        ])->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $this->assertNotSame($oldSession, session()->getId());
        $this->assertDatabaseHas('activity_logs', ['action' => 'login', 'user_id' => $user->id]);
        $this->assertNotNull($user->fresh()->last_login_at);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $this->makeUser('admin', ['email' => 'inactive@example.test', 'password' => 'secret1234', 'is_active' => false]);

        $this->post('/login', [
            'email' => 'inactive@example.test',
            'password' => 'secret1234',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->makeUser('admin', ['email' => 'admin@example.test']);

        $this->post('/login', [
            'email' => 'admin@example.test',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_attempts_are_rate_limited(): void
    {
        $this->makeUser('admin', ['email' => 'throttle@example.test']);

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/login', [
                'email' => 'throttle@example.test',
                'password' => 'bad-password',
            ])->assertSessionHasErrors('email');
        }

        $this->post('/login', [
            'email' => 'throttle@example.test',
            'password' => 'bad-password',
        ])->assertStatus(429);
    }

    public function test_user_can_logout_and_activity_is_recorded(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post('/logout')->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('activity_logs', ['action' => 'logout', 'user_id' => $user->id]);
    }
}
