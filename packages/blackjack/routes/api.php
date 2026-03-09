<?php

use Amu\Blackjack\Http\Controllers\Api\V1\BlackjackRoundController;
use Amu\Blackjack\Http\Controllers\Api\V1\BlackjackTableController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/blackjack')
    ->middleware('auth:sanctum')
    ->name('api.v1.blackjack.')
    ->group(function () {
        Route::post('/rooms/{room}/open', [BlackjackTableController::class, 'open'])->name('rooms.open');
        Route::get('/tables/{table}', [BlackjackTableController::class, 'show'])->name('tables.show');
        Route::post('/tables/{table}/seats', [BlackjackTableController::class, 'seat'])->name('tables.seat');

        Route::post('/tables/{table}/rounds', [BlackjackRoundController::class, 'store'])->name('rounds.store');
        Route::post('/rounds/{round}/bets', [BlackjackRoundController::class, 'bet'])->name('rounds.bet');
        Route::post('/rounds/{round}/deal', [BlackjackRoundController::class, 'deal'])->name('rounds.deal');
        Route::post('/rounds/{round}/hit', [BlackjackRoundController::class, 'hit'])->name('rounds.hit');
        Route::post('/rounds/{round}/stand', [BlackjackRoundController::class, 'stand'])->name('rounds.stand');
        Route::post('/rounds/{round}/settle', [BlackjackRoundController::class, 'settle'])->name('rounds.settle');
    });
