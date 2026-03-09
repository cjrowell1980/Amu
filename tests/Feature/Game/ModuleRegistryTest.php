<?php

namespace Tests\Feature\Game;

use Amu\Core\Contracts\GameModule;
use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Events\ModuleRegistered;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ModuleRegistryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_module_can_be_registered_and_enabled(): void
    {
        Event::fake();

        $module = new class implements GameModule {
            public function slug(): string { return 'test-blackjack'; }
            public function name(): string { return 'Blackjack Placeholder'; }
            public function version(): string { return '0.1.0'; }
            public function description(): ?string { return 'Test module.'; }
            public function settings(): array { return ['tables' => true]; }
            public function boot(ModuleRegistry $registry): void {}
        };

        $registry = app(ModuleRegistry::class);
        $record = $registry->register($module);
        $registry->enable($record);

        $this->assertDatabaseHas('game_modules', [
            'slug' => 'test-blackjack',
            'enabled' => true,
        ]);

        Event::assertDispatched(ModuleRegistered::class);
    }
}
