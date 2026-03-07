<?php

namespace App\Services;

use App\Contracts\GameModuleInterface;
use InvalidArgumentException;

/**
 * Maintains the runtime registry of bound GameModule implementations.
 *
 * Modules register themselves via AppServiceProvider (or a dedicated
 * GameServiceProvider). The registry maps game slug → module instance.
 *
 * The database Game record is the source of truth for metadata and availability;
 * this service provides runtime access to the module logic.
 */
class GameRegistryService
{
    /** @var array<string, GameModuleInterface> */
    private array $modules = [];

    /**
     * Register a game module with the platform.
     *
     * @throws InvalidArgumentException if a module with the same slug is already registered.
     */
    public function register(GameModuleInterface $module): void
    {
        $slug = $module->getSlug();

        if ($this->has($slug)) {
            throw new InvalidArgumentException(
                "A game module with slug [{$slug}] is already registered. " .
                "Each game module must have a unique slug."
            );
        }

        $this->modules[$slug] = $module;
    }

    public function has(string $slug): bool
    {
        return isset($this->modules[$slug]);
    }

    /**
     * Resolve a module by slug.
     *
     * @throws InvalidArgumentException if no module is registered for the slug.
     */
    public function resolve(string $slug): GameModuleInterface
    {
        if (! $this->has($slug)) {
            throw new InvalidArgumentException(
                "No game module registered for slug [{$slug}]. " .
                "Ensure the module is registered in AppServiceProvider::registerGameModules()."
            );
        }

        return $this->modules[$slug];
    }

    /**
     * @deprecated Use resolve() instead.
     */
    public function get(string $slug): GameModuleInterface
    {
        return $this->resolve($slug);
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
