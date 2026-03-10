<?php

use Amu\Core\Http\Controllers\Admin\DashboardController;
use Amu\Core\Http\Controllers\Admin\ModuleController;
use Amu\Core\Http\Controllers\Admin\RoomController;
use Amu\Core\Http\Controllers\Admin\SessionController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SitePageController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'permission:access admin area'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::middleware('permission:manage users')->group(function () {
            Route::get('/users', [UserController::class, 'index'])->name('users.index');
            Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
            Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.roles.update');
        });

        Route::middleware('permission:manage roles')->group(function () {
            Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
            Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
            Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        });

        Route::middleware('permission:manage modules')->get('/modules', [ModuleController::class, 'index'])->name('modules.index');
        Route::middleware('permission:manage modules')->post('/modules/{module}/toggle', [ModuleController::class, 'toggle'])->name('modules.toggle');
        Route::middleware('permission:manage rooms')->get('/rooms', [RoomController::class, 'index'])->name('rooms.index');
        Route::middleware('permission:manage sessions')->get('/sessions', [SessionController::class, 'index'])->name('sessions.index');
        Route::middleware('permission:manage platform')->get('/pages', [SitePageController::class, 'index'])->name('pages.index');
        Route::middleware('permission:manage platform')->get('/pages/{page}/edit', [SitePageController::class, 'edit'])->name('pages.edit');
        Route::middleware('permission:manage platform')->put('/pages/{page}', [SitePageController::class, 'update'])->name('pages.update');
        Route::middleware('permission:view audit logs')->get('/audit', [AuditLogController::class, 'index'])->name('audit.index');
    });
