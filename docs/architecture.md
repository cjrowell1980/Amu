# Platform Architecture

## Overview

Amu is a **Laravel 12 monolith** providing a reusable platform core for modular multiplayer games. The architecture separates the **platform spine** (auth, rooms, sessions, presence, audit) from **game modules** (actual game logic), allowing new games to be built without changing the core.

```
┌──────────────────────────────────────────────────────────┐
│                       HTTP / WebSocket                    │
├──────────────────────────────────────────────────────────┤
│   API Controllers (v1)    │   Admin Controllers (Blade)   │
├───────────────────────────┴───────────────────────────────┤
│                     Actions / Services                     │
│   CreateRoomAction  │  JoinRoomAction  │  LeaveRoomAction  │
│   CreateSessionAction │ StartSessionAction               │
│   RoomLifecycleService  │  SessionLifecycleService       │
│   AuditService          │  GameRegistryService           │
├──────────────────────────────────────────────────────────┤
│                      Domain Models                        │
│  User  UserProfile  Game  GameRoom  GameRoomMember        │
│  GameSession  GameSessionParticipant  AuditLog            │
├──────────────────────────────────────────────────────────┤
│               Game Module Interface                       │
│   AbstractGameModule  →  ExampleGameModule               │
│                       →  (Future: PokerModule, etc.)     │
├──────────────────────────────────────────────────────────┤
│           Infrastructure                                  │
│  MariaDB  │  Redis  │  Laravel Reverb (WebSocket)        │
│  Laravel Horizon (Queue)  │  Laravel Telescope (Debug)   │
└──────────────────────────────────────────────────────────┘
```

## Key Architectural Decisions

### Monolith over microservices
All platform code lives in one Laravel application. Game modules are PHP classes in `app/Modules/`, not separate services. This keeps deployment simple and avoids distributed systems complexity for a games platform that doesn't require it.

### Actions for domain logic
Business operations live in `app/Actions/` classes (one action per operation), not in controllers. Controllers are thin dispatchers. This keeps logic testable in isolation.

### Services for orchestration
Multi-step orchestration (lifecycle transitions, audit, broadcasting) lives in `app/Services/`. Services are registered as singletons in the container.

### Enums for all status fields
All status/type columns use PHP 8.1+ backed enums in `app/Enums/`. This eliminates string literal bugs, provides IDE autocomplete, and centralises valid state transitions.

### Events + Reverb for real-time
Platform events are broadcast via Laravel Reverb (WebSocket). Presence channels track who is in a room or session. Game modules broadcast via the session channel.

## Directory Structure

```
app/
├── Actions/          # One class per user-initiated operation
│   ├── Auth/
│   ├── Room/
│   └── Session/
├── Contracts/        # Interfaces (GameModuleInterface)
├── Enums/            # Status enums (RoomStatus, SessionStatus, etc.)
├── Events/           # Broadcastable events
│   ├── Room/
│   ├── Session/
│   └── Player/
├── Http/
│   ├── Controllers/
│   │   ├── Admin/    # Blade-rendered operator pages
│   │   └── Api/V1/   # JSON API controllers
│   ├── Requests/     # Form request validation
│   └── Resources/    # API response transformers
├── Models/           # Eloquent models
├── Modules/          # Game module implementations
│   ├── AbstractGameModule.php
│   └── ExampleGame/
├── Policies/         # Laravel authorization policies
├── Providers/        # Service providers
└── Services/         # Singleton service classes

database/
├── factories/        # Model factories for testing
├── migrations/       # Database schema (versioned)
└── seeders/          # Seed data for local dev

routes/
├── api.php           # REST API (/api/v1/...)
├── channels.php      # WebSocket channel auth
└── web.php           # Admin panel

tests/
├── Feature/
│   ├── Auth/
│   ├── Admin/
│   ├── Game/
│   ├── Lifecycle/    # State machine tests
│   ├── Profile/
│   ├── Room/
│   └── Session/
└── Unit/
```
