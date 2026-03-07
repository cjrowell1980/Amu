<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_user_can_register(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Test Player',
            'email' => 'player@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertCreated()
            ->assertJsonStructure([
                'token',
                'token_type',
                'user' => ['id', 'name', 'email', 'roles'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'player@example.com']);
        $this->assertDatabaseHas('user_profiles', ['display_name' => 'Test Player']);
    }

    public function test_registration_assigns_player_role(): void
    {
        $this->postJson('/api/v1/auth/register', [
            'name' => 'New Player',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'device_name' => 'test-device',
        ]);

        $user = User::where('email', 'new@example.com')->first();
        $this->assertTrue($user->hasRole('player'));
    }

    public function test_user_can_login(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'password',
            'device_name' => 'test-device',
        ]);

        $response->assertOk()
            ->assertJsonStructure(['token', 'token_type', 'user']);
    }

    public function test_login_fails_with_wrong_credentials(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
            'device_name' => 'test-device',
        ]);

        $response->assertUnprocessable();
    }

    public function test_authenticated_user_can_fetch_self(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/v1/auth/me');

        $response->assertOk()
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $this->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withToken($token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        // Token should be revoked — verify the token record is gone.
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
