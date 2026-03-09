# Game Module Guide

## Boundary

The host Laravel app owns authentication and package installation.
Generic platform concerns live in `packages/core`.
Game-specific rules, persistence, and UI stay inside each game package.

## Local Development Structure

```text
packages/
  core/
  blackjack/
```

## Registering a Module

Each game package should provide:

- `composer.json`
- a service provider
- a module class implementing `Amu\Core\Contracts\GameModule`
- its own migrations, services, routes, and tests

Example:

```php
use Amu\Core\Contracts\GameModule;
use Amu\Core\Contracts\ModuleRegistry;

final class BlackjackModule implements GameModule
{
    public function slug(): string { return 'blackjack'; }
    public function name(): string { return 'Blackjack / 21s'; }
    public function version(): string { return '0.1.0'; }
    public function description(): ?string { return 'Blackjack game module.'; }
    public function settings(): array { return []; }
    public function boot(ModuleRegistry $registry): void {}
}
```

Register it from the package service provider:

```php
public function boot(ModuleRegistry $registry): void
{
    $registry->register(new BlackjackModule());
}
```
