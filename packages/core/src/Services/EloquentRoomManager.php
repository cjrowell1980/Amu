<?php

namespace Amu\Core\Services;

use Amu\Core\Contracts\RoomManager;
use Amu\Core\Enums\ParticipantConnection;
use Amu\Core\Enums\ParticipantRole;
use Amu\Core\Enums\RoomStatus;
use Amu\Core\Events\PlayerJoinedRoom;
use Amu\Core\Events\PlayerLeftRoom;
use Amu\Core\Events\PlayerReadyChanged;
use Amu\Core\Events\RoomCreated;
use Amu\Core\Models\GameModule;
use Amu\Core\Models\GameRoom;
use Amu\Core\Models\GameRoomPlayer;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EloquentRoomManager implements RoomManager
{
    public function listVisibleFor(User $user): LengthAwarePaginator
    {
        return GameRoom::query()
            ->with(['module', 'players'])
            ->where(function ($query) use ($user) {
                $query->where('visibility', 'public')
                    ->orWhereHas('players', fn ($players) => $players->where('user_id', $user->id));
            })
            ->latest()
            ->paginate(15);
    }

    public function create(GameModule $module, User $host, array $attributes): GameRoom
    {
        return DB::transaction(function () use ($module, $host, $attributes) {
            $room = GameRoom::query()->create([
                'game_module_id' => $module->id,
                'host_user_id' => $host->id,
                'code' => Str::upper(Str::random(6)),
                'name' => $attributes['name'],
                'visibility' => $attributes['visibility'],
                'status' => $attributes['status'] ?? RoomStatus::Waiting,
                'min_players' => $attributes['min_players'],
                'max_players' => $attributes['max_players'],
                'settings' => $attributes['settings'] ?? [],
            ]);

            $participant = GameRoomPlayer::query()->create([
                'game_room_id' => $room->id,
                'user_id' => $host->id,
                'participation' => ParticipantRole::Joined,
                'connection_status' => ParticipantConnection::Connected,
                'joined_at' => now(),
            ]);

            event(new RoomCreated($room));
            event(new PlayerJoinedRoom($participant));

            return $room->load(['module', 'players']);
        });
    }

    public function join(GameRoom $room, User $user, string $participation = 'joined'): GameRoom
    {
        return DB::transaction(function () use ($room, $user, $participation) {
            if ($room->players()->where('user_id', $user->id)->exists()) {
                abort(422, 'Player is already in the room.');
            }

            $participant = GameRoomPlayer::query()->create([
                'game_room_id' => $room->id,
                'user_id' => $user->id,
                'participation' => $participation,
                'connection_status' => ParticipantConnection::Connected,
                'joined_at' => now(),
            ]);

            event(new PlayerJoinedRoom($participant));

            return $room->refresh()->load(['module', 'players']);
        });
    }

    public function leave(GameRoom $room, User $user): GameRoom
    {
        return DB::transaction(function () use ($room, $user) {
            $participant = GameRoomPlayer::query()
                ->where('game_room_id', $room->id)
                ->where('user_id', $user->id)
                ->whereNull('left_at')
                ->firstOrFail();

            $participant->forceFill([
                'left_at' => now(),
                'is_ready' => false,
                'connection_status' => ParticipantConnection::Disconnected,
            ])->save();

            if ($room->host_user_id === $user->id) {
                $nextHost = GameRoomPlayer::query()
                    ->where('game_room_id', $room->id)
                    ->whereNull('left_at')
                    ->where('user_id', '!=', $user->id)
                    ->oldest('joined_at')
                    ->first();

                if ($nextHost) {
                    $room->forceFill(['host_user_id' => $nextHost->user_id])->save();
                } else {
                    $room->forceFill(['status' => RoomStatus::Cancelled])->save();
                }
            }

            event(new PlayerLeftRoom($participant));

            return $room->refresh()->load(['module', 'players']);
        });
    }

    public function setReady(GameRoom $room, User $user, bool $isReady): GameRoom
    {
        $participant = GameRoomPlayer::query()
            ->where('game_room_id', $room->id)
            ->where('user_id', $user->id)
            ->whereNull('left_at')
            ->firstOrFail();

        $participant->forceFill(['is_ready' => $isReady])->save();

        event(new PlayerReadyChanged($participant));

        return $room->refresh()->load(['module', 'players']);
    }
}
