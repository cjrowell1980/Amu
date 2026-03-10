<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MemberDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()->loadMissing('roles', 'profile');

        return view('members.dashboard', [
            'user' => $user,
            'canAccessAdmin' => $user->can('access admin area'),
        ]);
    }
}
