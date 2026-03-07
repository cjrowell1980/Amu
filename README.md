# Amu — Multiplayer Game Platform Core

A modular, extensible Laravel 12 platform spine for building real-time multiplayer party games.  
This repository contains the **platform core only** — no game logic is included.

---

## Stack

| Layer | Technology |
|---|---|
| Framework | Laravel 12 |
| Language | PHP 8.3+ |
| Database | MariaDB 11 / MySQL 8 |
| Cache / Queue / Session | Redis |
| API Auth | Laravel Sanctum |
| Roles & Permissions | Spatie Laravel Permission |
| Real-time Broadcasting | Laravel Reverb (WebSockets) |
| Queue Monitor | Laravel Horizon |
| Request Inspector | Laravel Telescope |
| Containerisation | Docker + Docker Compose |
| UI (admin only) | Blade (no SPA) |

---

## Architecture Overview

```
app/
├── Actions/                    # Single-responsibility action classes
│   ├── Auth/
│   │   └── RegisterUserAction.php
│   ├── Room/
│   │   ├── CreateRoomAction.php
│   │   ├── JoinRoomAction.php
│   │   └── LeaveRoomAction.php
│   └── Session/
│       └── CreateSessionAction.php
│
├── Contracts/
│   └── GameModuleInterface.php # Contract every game module MUST implement
│
├── Events/
│   ├── Room/                   # PlayerJoinedRoom, PlayerLeftRoom, RoomCreated,
│   │   ...                     # RoomUpdated, RoomReadyStateChanged
│   └── Session/                # SessionCreated, SessionStarted
│
├── Http/
│   ├── Controllers/
│   │   ├── Api/V1/             # Versioned JSON API controllers
│   │   └── Admin/              # Internal Blade admin controllers
│   ├── Requests/               # Form request validation
│   └── Resources/              # API resource transformers
│
├── Models/                     # Eloquent models
│   ├── User, UserProfile
│   ├── Game
│   ├── GameRoom, GameRoomMember
│   └── GameSession, GameSessionParticipant
│
├── Modules/
│   └── ExampleGame/            # Stub proving the module contract works
│       └── ExampleGameModule.php
│
├── Policies/
│   └── GameRoomPolicy.php
│
├── Providers/
│   ├── AppServiceProvider.php  # Singleton registry + module registration
│   ├── AuthServiceProvider.php # Policy + Horizon/Telescope gate
│   └── TelescopeServiceProvider.php
│
└── Services/
    └── GameRegistryService.php # Runtime slug → GameModule resolver
```

### Layering Rules

- **Controllers** are thin — they delegate to Actions and return Resources.
- **Actions** own all business logic and event dispatching.
- **Services** are stateful singletons shared across a request (e.g. `GameRegistryService`).
- **Events** are broadcast over Reverb channels; game modules must not dispatch platform events.
- **Contracts** decouple platform from game implementations — game modules only implement the interface.

---

## Database Schema

```
users                       ← Laravel default + HasRoles, HasApiTokens
user_profiles               ← 1:1 with users (display_name, avatar, status)
games                       ← Registry of available games (enabled flag, slug, module_class)
game_rooms                  ← Lobbies (code, host, visibility, status, password_hash)
game_room_members           ← Pivot: user ↔ room (role, is_ready, team, seat)
game_sessions               ← Match records (uuid, status lifecycle, config snapshot)
game_session_participants   ← Pivot: user ↔ session (role, score, rank, result_detail)
```

All tables use `id` (bigint), `created_at`, `updated_at`. Soft deletes on `games`, `game_rooms`, `game_sessions`.

---

## API Reference (v1)

Base path: `/api/v1`  
Auth: Bearer token (Sanctum) — required for all routes except register/login.

### Auth

| Method | Path | Description |
|---|---|---|
| POST | `/auth/register` | Register + get token |
| POST | `/auth/login` | Login + get token |
| GET | `/auth/me` | Current user + profile |
| POST | `/auth/logout` | Revoke current token |

### Games

| Method | Path | Description |
|---|---|---|
| GET | `/games` | List enabled games |
| GET | `/games/{slug}` | Single game details |

### Rooms

| Method | Path | Description |
|---|---|---|
| GET | `/rooms` | List public waiting rooms |
| POST | `/rooms` | Create a room |
| GET | `/rooms/{id}` | Room details (policy-gated) |
| POST | `/rooms/{id}/join` | Join a room |
| DELETE | `/rooms/{id}/leave` | Leave a room |
| POST | `/rooms/{id}/ready` | Toggle ready state |

---

## Real-time Channels

All events implement `ShouldBroadcast` and are broadcast via **Laravel Reverb**.

| Channel | Events |
|---|---|
| `platform.lobby` (public) | `room.created`, `room.updated` |
| `presence-room.{id}` | `player.joined`, `player.left`, `room.ready_state_changed`, `session.created` |
| `presence-session.{id}` | `session.started` |

Clients subscribe using Laravel Echo. Example:

```js
Echo.join(`room.${roomId}`)
    .listen('.player.joined', (e) => console.log(e))
    .listen('.room.ready_state_changed', (e) => console.log(e));
```

---

## Adding a Game Module

1. Create a class that implements `App\Contracts\GameModuleInterface`.
2. Place it in `app/Modules/YourGame/YourGameModule.php`.
3. Register it in `AppServiceProvider::registerGameModules()`:

```php
$registry->register(new \App\Modules\YourGame\YourGameModule());
```

4. Add a `games` database record (or use the seeder) with the matching `slug` and `module_class`.
5. Set `enabled = true` when ready.

The platform will automatically resolve your module at runtime via `GameRegistryService::get($slug)`.

### What the contract requires

| Method | Called when |
|---|---|
| `validateRoomConfig(array)` | Creating/updating a room |
| `buildSessionConfig(GameRoom, array)` | Session created |
| `onSessionStart(GameSession)` | Session transitions to active |
| `handlePlayerAction(...)` | Player submits an action |
| `getPublicState(GameSession)` | Any client polls public state |
| `getPrivateState(GameSession, User)` | Authenticated player polls private state |
| `onSessionEnd(GameSession)` | Session ended — return result summary |
| `persistResults(GameSession, array)` | After `onSessionEnd` — write per-player data |

---

## Roles & Permissions

| Role | Capabilities |
|---|---|
| `player` | Create rooms |
| `moderator` | + moderate/force-close rooms, view/end sessions |
| `operator` | + manage game registry, Telescope, Horizon |
| `admin` | All permissions |

Assign via Spatie:
```php
$user->assignRole('admin');
$user->hasRole('moderator');
```

---

## Admin Panel

Accessible at `/admin` — requires `admin` or `operator` role.

| Route | Description |
|---|---|
| `/admin` | Dashboard with platform stats |
| `/admin/users` | User list + detail |
| `/admin/games` | Game registry + enable/disable toggle |
| `/admin/rooms` | All rooms + detail |
| `/admin/sessions` | All sessions + detail |
| `/telescope` | Telescope request inspector |
| `/horizon` | Horizon queue dashboard |

---

## Local Development Setup

### Prerequisites
- Docker + Docker Compose
- (Optional) PHP 8.3 + Composer for running commands locally

### Quick Start

```bash
# 1. Clone and enter project
git clone <repo-url>
cd amu

# 2. Copy environment file
cp .env.example .env

# 3. Start containers
docker compose up -d

# 4. Install PHP dependencies (if not pre-built)
docker compose exec app composer install

# 5. Generate app key
docker compose exec app php artisan key:generate

# 6. Run migrations and seeders
docker compose exec app php artisan migrate --seed

# 7. Access the platform
open http://localhost:8000
```

### Services

| Service | Port | Description |
|---|---|---|
| nginx | 8000 | Web server (configurable via `APP_PORT`) |
| app | — | PHP-FPM application |
| mysql | 3306 | MariaDB database |
| redis | 6379 | Cache / queue / sessions |
| horizon | — | Queue worker (auto-started) |
| reverb | 8080 | WebSocket server |

### Useful Commands

```bash
# Run tests
php artisan test

# Seed database (roles, games, admin user)
php artisan db:seed

# Queue dashboard
http://localhost:8000/horizon

# Request inspector
http://localhost:8000/telescope

# Run Horizon locally
php artisan horizon

# Run Reverb locally
php artisan reverb:start
```

### Default Credentials (after seeding)

| Account | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Operator | operator@example.com | password |

---

## Testing

```bash
# Run all tests
php artisan test

# Run specific suite
php artisan test --filter AuthenticationTest
php artisan test --filter GameRegistryTest
php artisan test --filter RoomManagementTest
```

Tests use SQLite in-memory and do not require Docker.  
27 tests cover: authentication, game registry, room lifecycle (create/join/leave/ready/auth failures).

---

## Future Extension Points

| Feature | Where to add |
|---|---|
| Leaderboards | New `leaderboard_entries` table + service + API endpoint |
| Economy / rewards | New `wallet_transactions` table + economy service |
| Moderation | Expand `GameRoomPolicy` + moderation event log table |
| Team management | `GameRoomMember.team_number` already present |
| Game-specific channels | Add presence channels in `routes/channels.php` |
| Mobile push | Add `FcmToken` model, hook into notification system |
| Spectator streams | `allow_spectators` flag + spectator-only channel |
| Replay | Persist action log in `game_sessions.session_config` or separate table |
