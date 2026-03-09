<?php

use Amu\Core\Http\Controllers\Admin\DashboardController;
use Amu\Core\Http\Controllers\Admin\ModuleController;
use Amu\Core\Http\Controllers\Admin\RoomController;
use Amu\Core\Http\Controllers\Admin\SessionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');
        Route::get('/modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::post('/modules/{module}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
        Route::get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
    });
