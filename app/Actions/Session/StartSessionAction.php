<?php

namespace App\Actions\Session;

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Services\SessionLifecycleService;
use Illuminate\Validation\ValidationException;

class StartSessionAction
{
    public function __construct(private readonly SessionLifecycleService $lifecycle) {}

    public function execute(GameSession $session): GameSession
    {
        if (! $session->canTransitionTo(SessionStatus::Starting)) {
            throw ValidationException::withMessages([
                'session' => "Session cannot be started from status [{$session->status->value}].",
            ]);
        }

        return $this->lifecycle->startSession($session);
    }
}
