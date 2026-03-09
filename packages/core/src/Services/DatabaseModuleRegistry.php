<?php

namespace Amu\Core\Services;

use Amu\Core\Contracts\GameModule as GameModuleContract;
use Amu\Core\Contracts\ModuleRegistry;
use Amu\Core\Events\ModuleRegistered;
use Amu\Core\Models\GameModule;
use Illuminate\Support\Collection;

class DatabaseModuleRegistry implements ModuleRegistry
{
    public function register(GameModuleContract $module): GameModule
    {
        $record = GameModule::query()->updateOrCreate(
            ['slug' => $module->slug()],
            [
                'name' => $module->name(),
                'package_name' => str_replace('\\', '/', $module::class),
                'provider_class' => $module::class,
                'version' => $module->version(),
                'description' => $module->description(),
                'settings' => $module->settings(),
            ],
        );

        event(new ModuleRegistered($record));
        $module->boot($this);

        return $record;
    }

    public function all(): Collection
    {
        return GameModule::query()->orderBy('name')->get();
    }

    public function enabled(): Collection
    {
        return GameModule::query()->where('enabled', true)->orderBy('name')->get();
    }

    public function findBySlug(string $slug): ?GameModule
    {
        return GameModule::query()->where('slug', $slug)->first();
    }

    public function enable(GameModule $module): GameModule
    {
        $module->forceFill(['enabled' => true])->save();

        return $module->refresh();
    }

    public function disable(GameModule $module): GameModule
    {
        $module->forceFill(['enabled' => false])->save();

        return $module->refresh();
    }
}
