<?php

namespace App\Services;

use App\Contracts\GameModuleInterface;
use App\Models\Game;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * Maintains the runtime registry of bound GameModule implementations.
 *
 * Modules register themselves via AppServiceProvider (or a dedicated
 * GameServiceProvider). The registry maps game slug → module instance.
 * The database Game record is the source of truth for metadata; this
 * service provides runtime access to the module logic.
 */
class GameRegistryService
{
    /** @var array<string, GameModuleInterface> */
    private array $modules = [];

    public function register(GameModuleInterface $module): void
    {
        $this->modules[$module->getSlug()] = $module;
    }

    public function has(string $slug): bool
    {
        return isset($this->modules[$slug]);
    }

    public function get(string $slug): GameModuleInterface
    {
        if (! $this->has($slug)) {
            throw new InvalidArgumentException("No game module registered for slug: {$slug}");
        }

        return $this->modules[$slug];
    }

    /** @return array<string, GameModuleInterface> */
    public function all(): array
    {
        return $this->modules;
    }

    public function registeredSlugs(): array
    {
        return array_keys($this->modules);
    }
}
