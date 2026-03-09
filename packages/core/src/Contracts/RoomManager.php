<?php

namespace Amu\Core\Contracts;

use Amu\Core\Models\GameModule;
use Amu\Core\Models\GameRoom;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface RoomManager
{
    public function listVisibleFor(User $user): LengthAwarePaginator;

    public function create(GameModule $module, User $host, array $attributes): GameRoom;

    public function join(GameRoom $room, User $user, string $participation = 'joined'): GameRoom;

    public function leave(GameRoom $room, User $user): GameRoom;

    public function setReady(GameRoom $room, User $user, bool $isReady): GameRoom;
}
