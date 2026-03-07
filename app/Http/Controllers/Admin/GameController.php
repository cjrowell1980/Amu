<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;

class GameController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:admin|operator']);
    }

    public function index()
    {
        $games = Game::withTrashed()->withCount('rooms')->orderBy('name')->get();

        return view('admin.games.index', compact('games'));
    }

    public function show(Game $game)
    {
        $game->load('rooms');

        return view('admin.games.show', compact('game'));
    }

    public function toggleEnabled(Game $game)
    {
        $game->update(['enabled' => ! $game->enabled]);

        return back()->with('success', "Game '{$game->name}' " . ($game->enabled ? 'enabled' : 'disabled') . '.');
    }
}
