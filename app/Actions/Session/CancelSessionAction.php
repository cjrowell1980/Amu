<?php

namespace App\Actions\Session;

use App\Models\GameSession;
use App\Services\SessionLifecycleService;
use Illuminate\Validation\ValidationException;
use App\Enums\SessionStatus;

class CancelSessionAction
{
    public function __construct(private readonly SessionLifecycleService $lifecycle) {}

    public function execute(GameSession $session, string $reason = 'manual'): GameSession
    {
        if ($session->status->isTerminal()) {
            throw ValidationException::withMessages([
                'session' => "Session is already in a terminal state [{$session->status->value}].",
            ]);
        }

        return $this->lifecycle->cancelSession($session, $reason);
    }
}
