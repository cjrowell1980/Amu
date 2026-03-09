<?php

use Amu\Core\Http\Controllers\Api\V1\ModuleController;
use Amu\Core\Http\Controllers\Api\V1\RoomController;
use Amu\Core\Http\Controllers\Api\V1\SessionController;
use Amu\Core\Http\Controllers\Api\V1\WalletController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')
    ->middleware('auth:sanctum')
    ->name('api.v1.')
    ->group(function () {
        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');

        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::post('/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::get('/rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
        Route::post('/rooms/{room}/join', [RoomController::class, 'join'])->name('rooms.join');
        Route::delete('/rooms/{room}/leave', [RoomController::class, 'leave'])->name('rooms.leave');
        Route::post('/rooms/{room}/ready', [RoomController::class, 'ready'])->name('rooms.ready');

        Route::post('/rooms/{room}/sessions', [SessionController::class, 'store'])->name('sessions.store');
        Route::get('/sessions/{session}', [SessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/transition', [SessionController::class, 'transition'])->name('sessions.transition');

        Route::get('/wallet', [WalletController::class, 'show'])->name('wallet.show');
    });
