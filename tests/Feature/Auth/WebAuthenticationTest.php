<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_login_page_is_rendered(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign In');
    }

    public function test_admin_can_sign_in_through_web_form(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');

        $response = $this->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('members.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_members_dashboard_shows_admin_area_link_when_user_has_access(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)
            ->get(route('members.dashboard'))
            ->assertOk()
            ->assertSee('Members Area')
            ->assertSee('Open Admin Area');
    }

    public function test_members_dashboard_hides_admin_area_link_for_regular_players(): void
    {
        $user = User::factory()->create();
        $user->assignRole('player');

        $this->actingAs($user)
            ->get(route('members.dashboard'))
            ->assertOk()
            ->assertSee('Members Area')
            ->assertDontSee('Open Admin Area');
    }

    public function test_admin_can_access_user_and_role_management_pages(): void
    {
        $user = User::factory()->create();
        $user->assignRole('admin');

        $this->actingAs($user)->get(route('admin.users.index'))->assertOk();
        $this->actingAs($user)->get(route('admin.roles.index'))->assertOk();
    }

    public function test_invalid_credentials_return_to_login(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('admin');

        $response = $this->from('/login')->post('/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }
}
