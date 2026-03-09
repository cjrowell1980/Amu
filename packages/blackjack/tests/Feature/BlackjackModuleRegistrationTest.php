<?php

namespace Tests\Feature\Blackjack;

use Amu\Core\Contracts\ModuleRegistry;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlackjackModuleRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_blackjack_module_is_registered_with_the_core_registry(): void
    {
        $registry = app(ModuleRegistry::class);
        $module = $registry->findBySlug('blackjack');

        $this->assertNotNull($module);
        $this->assertSame('Blackjack', $module->name);
        $this->assertTrue($module->enabled);
    }
}
