<?php

namespace Amu\Core\Events;

use Amu\Core\Models\GameSession;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionStarted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public GameSession $session)
    {
    }
}
