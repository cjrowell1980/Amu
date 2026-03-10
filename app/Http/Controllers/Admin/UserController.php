<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['profile', 'roles'])->latest()->paginate(30);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['profile', 'roles']);

        return view('admin.users.show', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function updateRoles(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'roles' => ['array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $roles = $validated['roles'] ?? [];

        $user->syncRoles($roles);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', "Roles updated for {$user->name}.");
    }
}
