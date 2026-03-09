<?php

namespace Amu\Core\Contracts;

interface GameModule
{
    public function slug(): string;

    public function name(): string;

    public function version(): string;

    public function description(): ?string;

    public function settings(): array;

    public function boot(ModuleRegistry $registry): void;
}
