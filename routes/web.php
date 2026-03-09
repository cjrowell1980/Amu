<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Web\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

// ── Auth routes (session-based for web/admin login) ───────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

// ── Admin / Operator ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|operator|moderator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

        // Dashboard (admin + operator only)
        Route::middleware('role:admin|operator')
            ->get('/', [Admin\DashboardController::class, 'index'])
            ->name('dashboard');

        // Users (admin only)
        Route::middleware('role:admin')->group(function () {
            Route::get('/users',        [Admin\UserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');
        });

        // Games (admin + operator)
        Route::middleware('role:admin|operator')->group(function () {
            Route::get('/games',                         [Admin\GameController::class, 'index'])->name('games.index');
            Route::get('/games/{game}',                  [Admin\GameController::class, 'show'])->name('games.show');
            Route::post('/games/{game}/toggle',          [Admin\GameController::class, 'toggleEnabled'])->name('games.toggle');
            Route::post('/games/{game}/availability',    [Admin\GameController::class, 'setAvailability'])->name('games.availability');
        });

        // Rooms (admin + operator + moderator)
        Route::get('/rooms',              [Admin\RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/{room}',       [Admin\RoomController::class, 'show'])->name('rooms.show');
        Route::post('/rooms/{room}/close',  [Admin\RoomController::class, 'close'])->name('rooms.close');
        Route::post('/rooms/{room}/cancel', [Admin\RoomController::class, 'cancel'])->name('rooms.cancel');

        // Sessions (admin + operator + moderator)
        Route::get('/sessions',                  [Admin\SessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/{session}',        [Admin\SessionController::class, 'show'])->name('sessions.show');
        Route::post('/sessions/{session}/cancel',[Admin\SessionController::class, 'cancel'])->name('sessions.cancel');

        // Audit log
        Route::middleware('role:admin|operator')
            ->get('/audit', [Admin\AuditLogController::class, 'index'])
            ->name('audit.index');
    });
