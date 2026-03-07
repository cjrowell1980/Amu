<?php

namespace App\Actions\Session;

use App\Events\Session\SessionCreated;
use App\Models\GameRoom;
use App\Models\GameSession;
use App\Models\GameSessionParticipant;
use App\Services\GameRegistryService;
use Illuminate\Validation\ValidationException;

class CreateSessionAction
{
    public function __construct(
        private readonly GameRegistryService $registry,
    ) {}

    public function execute(GameRoom $room): GameSession
    {
        if ($room->status !== 'waiting') {
            throw ValidationException::withMessages([
                'room' => 'A session can only be created from a waiting room.',
            ]);
        }

        if ($room->activeSession()->exists()) {
            throw ValidationException::withMessages([
                'room' => 'An active session already exists for this room.',
            ]);
        }

        $game = $room->game;
        $resolvedConfig = array_merge(
            $game->default_config ?? [],
            $room->room_config ?? [],
        );

        // If a module is registered, let it build the session config.
        if ($this->registry->has($game->slug)) {
            $module = $this->registry->get($game->slug);
            $resolvedConfig = $module->buildSessionConfig($room, $resolvedConfig);
        }

        $session = GameSession::create([
            'game_id' => $game->id,
            'game_room_id' => $room->id,
            'status' => 'created',
            'session_config' => $resolvedConfig,
        ]);

        // Snapshot room members as session participants.
        foreach ($room->activeMembers as $member) {
            GameSessionParticipant::create([
                'game_session_id' => $session->id,
                'user_id' => $member->user_id,
                'role' => $member->role === 'spectator' ? 'spectator' : 'player',
                'team_number' => $member->team_number,
                'seat_number' => $member->seat_number,
                'joined_at' => now(),
            ]);
        }

        $room->update(['status' => 'in_game']);

        event(new SessionCreated($session));

        return $session->load(['game', 'room', 'participants']);
    }
}
