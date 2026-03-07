<?php

namespace App\Actions\Session;

use App\Enums\SessionStatus;
use App\Models\GameSession;
use App\Services\SessionLifecycleService;
use Illuminate\Validation\ValidationException;

class CompleteSessionAction
{
    public function __construct(private readonly SessionLifecycleService $lifecycle) {}

    public function execute(GameSession $session, array $resultSummary = []): GameSession
    {
        if (! $session->canTransitionTo(SessionStatus::Completed)) {
            throw ValidationException::withMessages([
                'session' => "Session cannot be completed from status [{$session->status->value}].",
            ]);
        }

        return $this->lifecycle->completeSession($session, $resultSummary);
    }
}
