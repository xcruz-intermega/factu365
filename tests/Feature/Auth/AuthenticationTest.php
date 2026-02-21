<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithTenancy;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithTenancy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWithTenancy();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get($this->tenantUrl('/login'));

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post($this->tenantUrl('/login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post($this->tenantUrl('/login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post($this->tenantUrl('/logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
