<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GameAvailability;
use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Services\AuditService;
use Illuminate\Http\Request;

class GameController extends Controller
{

    public function index()
    {
        $games = Game::withTrashed()
            ->withCount(['rooms', 'sessions'])
            ->orderBy('name')
            ->get();

        $availabilities = GameAvailability::cases();

        return view('admin.games.index', compact('games', 'availabilities'));
    }

    public function show(Game $game)
    {
        $game->load(['rooms' => fn ($q) => $q->latest()->limit(10), 'sessions' => fn ($q) => $q->latest()->limit(10)]);

        return view('admin.games.show', compact('game'));
    }

    /**
     * Update a game's availability (enabled/beta/hidden/disabled).
     */
    public function setAvailability(Request $request, Game $game, AuditService $audit)
    {
        $request->validate([
            'availability' => ['required', 'in:' . implode(',', array_column(GameAvailability::cases(), 'value'))],
        ]);

        $from = $game->availability->value;
        $to   = $request->input('availability');

        $game->update(['availability' => $to]);

        $audit->gameAvailabilityChanged($game, $from, $to);

        return back()->with('success', "Game '{$game->name}' availability set to '{$to}'.");
    }

    /**
     * Legacy toggle — kept for convenience (toggles between enabled/disabled).
     */
    public function toggleEnabled(Game $game, AuditService $audit)
    {
        $from = $game->availability->value;
        $to   = $game->isEnabled() ? GameAvailability::Disabled : GameAvailability::Enabled;

        $game->update(['availability' => $to]);

        $audit->gameAvailabilityChanged($game, $from, $to->value);

        return back()->with('success', "Game '{$game->name}' " . ($game->isEnabled() ? 'enabled' : 'disabled') . '.');
    }
}
