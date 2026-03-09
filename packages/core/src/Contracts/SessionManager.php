<?php

namespace Amu\Core\Contracts;

use Amu\Core\Enums\SessionStatus;
use Amu\Core\Models\GameRoom;
use Amu\Core\Models\GameSession;

interface SessionManager
{
    public function create(GameRoom $room, array $attributes = []): GameSession;

    public function transition(GameSession $session, SessionStatus $status): GameSession;
}
