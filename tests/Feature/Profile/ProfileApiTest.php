<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    /** @test */
    public function authenticated_user_can_view_their_profile()
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $this->actingAs($user)
            ->getJson('/api/v1/profile')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id);
    }

    /** @test */
    public function user_can_update_display_name()
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $this->actingAs($user)
            ->patchJson('/api/v1/profile', ['display_name' => 'NewDisplayName'])
            ->assertOk()
            ->assertJsonPath('data.profile.display_name', 'NewDisplayName');
    }

    /** @test */
    public function guest_cannot_access_profile()
    {
        $this->getJson('/api/v1/profile')->assertUnauthorized();
    }
}
