<?php

namespace Amu\Core\Contracts;

use Amu\Core\Models\GameModule as GameModuleRecord;
use Illuminate\Support\Collection;

interface ModuleRegistry
{
    public function register(GameModule $module): GameModuleRecord;

    public function all(): Collection;

    public function enabled(): Collection;

    public function findBySlug(string $slug): ?GameModuleRecord;

    public function enable(GameModuleRecord $module): GameModuleRecord;

    public function disable(GameModuleRecord $module): GameModuleRecord;
}
