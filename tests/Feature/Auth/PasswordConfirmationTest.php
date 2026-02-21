<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\WithTenancy;

class PasswordConfirmationTest extends TestCase
{
    use RefreshDatabase, WithTenancy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpWithTenancy();
    }

    public function test_confirm_password_screen_can_be_rendered(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get($this->tenantUrl('/confirm-password'));

        $response->assertStatus(200);
    }

    public function test_password_can_be_confirmed(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post($this->tenantUrl('/confirm-password'), [
            'password' => 'password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    public function test_password_is_not_confirmed_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post($this->tenantUrl('/confirm-password'), [
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors();
    }
}
