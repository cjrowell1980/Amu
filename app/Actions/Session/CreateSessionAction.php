<?php

namespace App\Actions\Session;

use App\Enums\ConnectionStatus;
use App\Enums\ParticipantRole;
use App\Enums\RoomStatus;
use App\Enums\SessionStatus;
use App\Events\Session\SessionCreated;
use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use App\Services\AuditService;
use App\Services\GameRegistryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateSessionAction
{
    public function __construct(
        private readonly GameRegistryService $registry,
        private readonly AuditService $audit,
    ) {}

    public function execute(GameRoom $room): GameSession
    {
        $this->assertCanCreate($room);

        return DB::transaction(function () use ($room) {
            $game = $room->game;

            $resolvedConfig = array_merge(
                $game->default_config ?? [],
                $room->room_config ?? [],
            );

            // If a module is registered, let it build and validate the session config.
            if ($this->registry->has($game->slug)) {
                $module = $this->registry->resolve($game->slug);
                $resolvedConfig = $module->buildSessionConfig($room, $resolvedConfig);
            }

            $session = GameSession::create([
                'game_id'      => $game->id,
                'game_room_id' => $room->id,
                'status'       => SessionStatus::Created,
                'session_config' => $resolvedConfig,
            ]);

            // Snapshot active room members as session participants
            foreach ($room->activeMembers()->with('user')->get() as $member) {
                $participantRole = $member->isSpectator()
                    ? ParticipantRole::Spectator
                    : ParticipantRole::Player;

                GameSessionParticipant::create([
                    'game_session_id'  => $session->id,
                    'user_id'          => $member->user_id,
                    'role'             => $participantRole,
                    'team_number'      => $member->team_number,
                    'seat_number'      => $member->seat_number,
                    'connection_status'=> ConnectionStatus::Connected,
                    'joined_at'        => now(),
                    'last_seen_at'     => now(),
                ]);
            }

            // Move room to starting
            $room->status = RoomStatus::Starting;
            $room->save();

            $this->audit->sessionCreated($session);
            event(new SessionCreated($session));

            return $session->load(['game', 'room', 'participants.user.profile']);
        });
    }

    private function assertCanCreate(GameRoom $room): void
    {
        if (! in_array($room->status, [RoomStatus::Waiting, RoomStatus::Ready], strict: true)) {
            throw ValidationException::withMessages([
                'room' => 'A session can only be created from a waiting or ready room.',
            ]);
        }

        if ($room->activeSession()->exists()) {
            throw ValidationException::withMessages([
                'room' => 'An active session already exists for this room.',
            ]);
        }

        $game = $room->game;

        if (! $game->hasValidModule()) {
            throw ValidationException::withMessages([
                'game' => 'The game module for this room is not available.',
            ]);
        }

        $activePlayers = $room->activePlayers()->count();

        if ($activePlayers < $game->min_players) {
            throw ValidationException::withMessages([
                'room' => "Not enough players. Need at least {$game->min_players}, have {$activePlayers}.",
            ]);
        }
    }
}
