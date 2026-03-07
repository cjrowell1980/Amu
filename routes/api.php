<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\SessionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Version 1
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api by bootstrap/app.php.
| Prefix this group further with /v1 so all routes resolve as /api/v1/...
|
| Auth: Laravel Sanctum Bearer token (Authorization: Bearer <token>)
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Public auth ───────────────────────────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login',    [AuthController::class, 'login'])->name('auth.login');

    // ── Authenticated ─────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::get('/auth/me',       [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout',  [AuthController::class, 'logout'])->name('auth.logout');

        // Profile
        Route::get('/profile',   [ProfileController::class, 'show'])->name('profile.show');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

        // Games registry
        Route::get('/games',            [GameController::class, 'index'])->name('games.index');
        Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');

        // Rooms / lobby
        Route::get('/rooms',                      [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms',                     [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}',               [RoomController::class, 'show'])->name('rooms.show');
        Route::post('/rooms/{room}/join',         [RoomController::class, 'join'])->name('rooms.join');
        Route::delete('/rooms/{room}/leave',      [RoomController::class, 'leave'])->name('rooms.leave');
        Route::post('/rooms/{room}/ready',        [RoomController::class, 'toggleReady'])->name('rooms.ready');
        Route::post('/rooms/{room}/start',        [RoomController::class, 'startSession'])->name('rooms.start');

        // Sessions
        Route::get('/sessions/{session}',              [SessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/start',       [SessionController::class, 'start'])->name('sessions.start');
        Route::get('/sessions/{session}/state',        [SessionController::class, 'publicState'])->name('sessions.state');
        Route::get('/sessions/{session}/private-state',[SessionController::class, 'privateState'])->name('sessions.private-state');
        Route::post('/sessions/{session}/action',      [SessionController::class, 'action'])->name('sessions.action');
        Route::post('/sessions/reconnect',             [SessionController::class, 'reconnect'])->name('sessions.reconnect');
    });
});
