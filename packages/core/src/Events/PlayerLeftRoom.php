<?php

namespace Amu\Core\Events;

use Amu\Core\Models\GameRoomPlayer;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PlayerLeftRoom
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public GameRoomPlayer $participant)
    {
    }
}
