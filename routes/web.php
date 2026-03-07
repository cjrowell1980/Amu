<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Auth routes (session-based for web/admin login) ───────────────────────────
// Minimal stubs — replace with a real login view when adding a web UI.
Route::get('/login', fn () => redirect('/'))->name('login');
Route::post('/logout', function () {
    auth()->logout();
    return redirect('/');
})->name('logout');

// ── Admin / Operator ──────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:admin|operator'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::get('/users', [Admin\UserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [Admin\UserController::class, 'show'])->name('users.show');

        Route::get('/games', [Admin\GameController::class, 'index'])->name('games.index');
        Route::get('/games/{game}', [Admin\GameController::class, 'show'])->name('games.show');
        Route::post('/games/{game}/toggle', [Admin\GameController::class, 'toggleEnabled'])->name('games.toggle');

        Route::get('/rooms', [Admin\RoomController::class, 'index'])->name('rooms.index');
        Route::get('/rooms/{room}', [Admin\RoomController::class, 'show'])->name('rooms.show');

        Route::get('/sessions', [Admin\SessionController::class, 'index'])->name('sessions.index');
        Route::get('/sessions/{session}', [Admin\SessionController::class, 'show'])->name('sessions.show');
    });
