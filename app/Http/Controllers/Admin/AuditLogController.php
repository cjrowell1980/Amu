<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{

    public function index(Request $request)
    {
        $query = AuditLog::with('user')
            ->when($request->event, fn ($q, $e) => $q->where('event', $e))
            ->when($request->user_id, fn ($q, $u) => $q->where('user_id', $u))
            ->latest('created_at');

        $logs         = $query->paginate(50)->withQueryString();
        $distinctEvents = AuditLog::select('event')->distinct()->orderBy('event')->pluck('event');

        return view('admin.audit.index', compact('logs', 'distinctEvents'));
    }
}
