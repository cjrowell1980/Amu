<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with('profile')->withCount('roomMemberships')->latest()->paginate(30);

        return view('admin.users.index', compact('users'));
    }

    public function show(User $user)
    {
        $user->load(['profile', 'hostedRooms', 'sessionParticipations.session.game']);

        return view('admin.users.show', compact('user'));
    }
}
