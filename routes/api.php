<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\GameController;
use App\Http\Controllers\Api\V1\RoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Version 1
|--------------------------------------------------------------------------
|
| All routes are prefixed with /api/v1 by the bootstrap/app.php configuration.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Public Auth ──────────────────────────────────────────────────────────
    Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');

    // ── Authenticated ─────────────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::get('/auth/me', [AuthController::class, 'me'])->name('auth.me');
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('auth.logout');

        // Games registry
        Route::get('/games', [GameController::class, 'index'])->name('games.index');
        Route::get('/games/{game:slug}', [GameController::class, 'show'])->name('games.show');

        // Rooms / lobbies
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
        Route::post('/rooms/{room}/join', [RoomController::class, 'join'])->name('rooms.join');
        Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
        Route::post('/rooms/{room}/ready', [RoomController::class, 'toggleReady'])->name('rooms.ready');
    });
});
