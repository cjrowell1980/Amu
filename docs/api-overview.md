# API Overview

Base URL: `http://localhost:8000/api/v1`

Auth: All protected endpoints require `Authorization: Bearer <token>` (Sanctum).

## Standard response shapes

**Success:**
```json
{ "data": { ... } }
```

**Paginated list:**
```json
{ "data": [...], "links": {...}, "meta": {...} }
```

**Error:**
```json
{ "message": "Human-readable error.", "error": "machine_code" }
```

---

## Auth

| Method | Endpoint         | Auth | Description         |
|--------|------------------|------|---------------------|
| POST   | /auth/register   | No   | Register new user   |
| POST   | /auth/login      | No   | Login, get token    |
| GET    | /auth/me         | Yes  | Current user info   |
| POST   | /auth/logout     | Yes  | Revoke token        |

## Profile

| Method | Endpoint  | Auth | Description        |
|--------|-----------|------|--------------------|
| GET    | /profile  | Yes  | Get own profile    |
| PATCH  | /profile  | Yes  | Update own profile |

## Games

| Method | Endpoint            | Auth | Description                      |
|--------|---------------------|------|----------------------------------|
| GET    | /games              | Yes  | List available games             |
| GET    | /games/{slug}       | Yes  | Show single game                 |

Games list respects availability rules:
- Players see only `enabled` games
- `beta_tester` role sees `enabled` + `beta` games
- `operator`/`admin` roles see everything except `disabled`

## Rooms

| Method | Endpoint               | Auth | Description                        |
|--------|------------------------|------|------------------------------------|
| GET    | /rooms                 | Yes  | List public waiting/ready rooms    |
| POST   | /rooms                 | Yes  | Create a room                      |
| GET    | /rooms/{id}            | Yes  | Room detail (policy: visibility)   |
| POST   | /rooms/{id}/join       | Yes  | Join a room                        |
| DELETE | /rooms/{id}/leave      | Yes  | Leave a room                       |
| POST   | /rooms/{id}/ready      | Yes  | Toggle ready state                 |
| POST   | /rooms/{id}/start      | Yes  | Host: create session (starts game) |

**Create room body:**
```json
{
  "game_id": 1,
  "name": "My Room",
  "visibility": "public",
  "password": null,
  "max_players": 4,
  "allow_spectators": false,
  "room_config": {}
}
```

**Join room body:**
```json
{ "password": "optional", "as_spectator": false }
```

## Sessions

| Method | Endpoint                         | Auth | Description                     |
|--------|----------------------------------|------|---------------------------------|
| GET    | /sessions/{uuid}                 | Yes  | Session details                 |
| POST   | /sessions/{uuid}/start           | Yes  | Host: activate session          |
| GET    | /sessions/{uuid}/state           | Yes  | Public game state               |
| GET    | /sessions/{uuid}/private-state   | Yes  | Private state for this player   |
| POST   | /sessions/{uuid}/action          | Yes  | Submit a game action            |
| POST   | /sessions/reconnect              | Yes  | Reconnect with token            |

**Action body (game-defined):**
```json
{ "type": "your_action_type", ...params }
```

**Reconnect body:**
```json
{ "token": "64-char-reconnect-token" }
```

## Real-time channels (Reverb/WebSocket)

| Channel                        | Type     | Events                                              |
|--------------------------------|----------|-----------------------------------------------------|
| `platform.lobby`               | Public   | room.created, room.status_changed, room.closed      |
| `room.{id}`                    | Presence | player.joined, player.left, room.host_transferred   |
| `session.{uuid}`               | Presence | session.started, session.completed, player.disconnected |
| `private-player.{id}.session.{uuid}` | Private | private state pushes                          |

Connect with: `ws://localhost:8080/app/{REVERB_APP_KEY}`
