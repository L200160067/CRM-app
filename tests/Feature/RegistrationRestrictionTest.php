<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_registration_route_is_disabled(): void
    {
        $response = $this->get('/register');

        $response->assertNotFound();
    }

    public function test_public_registration_post_is_disabled(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertNotFound();
    }

    public function test_user_management_index_is_restricted_to_super_admin(): void
    {
        // 1. Guest
        $this->get(route('users.index'))
            ->assertRedirect(route('login'));

        // 2. Normal User / Admin
        $admin = User::factory()->create(['role' => Role::Admin]);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertForbidden();

        // 3. Super Admin
        $superAdmin = User::factory()->create(['role' => Role::SuperAdmin]);

        $this->actingAs($superAdmin)
            ->get(route('users.index'))
            ->assertOk();
    }
}
