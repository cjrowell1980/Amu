# Core Domain

## Entities

### User
Standard Laravel user extended with:
- `UserProfile` (display name, avatar, country, preferences, status)
- Spatie roles: `admin`, `operator`, `moderator`, `beta_tester`, `player`
- `last_seen_at` for online presence tracking

### Game
Registry entry for each game type.
- Controlled by `availability` (enabled / beta / hidden / disabled)
- Has `module_class` pointing to a `GameModuleInterface` implementation
- `default_config` provides baseline game settings

### GameRoom
A lobby where players gather before a session.

**Status lifecycle:**
```
waiting → ready → starting → in_progress → completed
       ↘ cancelled
       ↘ closed
```

| Status       | Meaning                                     |
|--------------|---------------------------------------------|
| `waiting`    | Open, accepting players; not all are ready  |
| `ready`      | All active players are ready                |
| `starting`   | Session being created; locking room         |
| `in_progress`| Session is active                           |
| `completed`  | Session ended normally                      |
| `cancelled`  | Cancelled before or during session          |
| `closed`     | Operator-initiated graceful closure         |

### GameRoomMember
Tracks a user's presence in a room.
- `role`: `host` / `player` / `spectator`
- `is_ready`: true once player has toggled ready
- `left_at`: null if still active

**Host rules:**
- Host is always the first member; `is_ready` defaults to true
- If host leaves, the next-oldest player is promoted
- If the room becomes empty after host leaves, it is cancelled

### GameSession
The actual game instance. Snapshotted from a room at session creation.
- `uuid`: public identifier (used in API routes and channels)
- `session_config`: frozen game state blob (populated by module)
- `result_summary`: final results once session ends

**Status lifecycle:**
```
pending → created → starting → active → completed
                            ↘ paused → active
                   ↘ cancelled
                   ↘ abandoned
```

| Status     | Meaning                                       |
|------------|-----------------------------------------------|
| `pending`  | Pre-creation placeholder                       |
| `created`  | Participants snapshotted; not yet started      |
| `starting` | Module `onSessionStart()` being invoked        |
| `active`   | Game is in progress                           |
| `paused`   | Game temporarily halted                       |
| `completed`| Game ended normally; results persisted         |
| `abandoned`| Game ended abnormally (disconnections, etc.)   |
| `cancelled`| Operator or system cancelled before completion |

### GameSessionParticipant
Snapshot of a room member's participation.
- `role`: `player` / `spectator`
- `connection_status`: `connected` / `disconnected` / `reconnecting`
- `reconnect_token`: short-lived token for reconnect authentication
- `final_rank`, `score`, `result_detail`: populated by module after session ends

### AuditLog
Immutable append-only record of platform events.
- `event`: machine-readable name (e.g. `room.created`)
- `subject`: polymorphic reference to the affected entity
- `metadata`: arbitrary JSON context

## Enums Reference

| Enum                 | Values                                                    |
|----------------------|-----------------------------------------------------------|
| `GameAvailability`   | `enabled`, `beta`, `hidden`, `disabled`                   |
| `RoomStatus`         | `waiting`, `ready`, `starting`, `in_progress`, `completed`, `cancelled`, `closed` |
| `RoomVisibility`     | `public`, `private`, `unlisted`                           |
| `RoomMemberRole`     | `host`, `player`, `spectator`                             |
| `SessionStatus`      | `pending`, `created`, `starting`, `active`, `paused`, `completed`, `abandoned`, `cancelled` |
| `ParticipantRole`    | `player`, `spectator`                                     |
| `ConnectionStatus`   | `connected`, `disconnected`, `reconnecting`               |
