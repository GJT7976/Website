<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Deliberately read-only: index() is the only action this controller (or
 * any route) exposes for audit_logs. See spec §33 — "do not create a
 * normal admin action to erase the audit log casually" — enforced here by
 * simply never registering a store/update/destroy route, not by a
 * permission check that could be bypassed or misconfigured later.
 */
class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::query()->with('administrator')->latest();

        if ($userId = $request->integer('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->string('action')->toString()) {
            $query->where('action', 'like', "%{$action}%");
        }

        if ($from = $request->date('from')) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to = $request->date('to')) {
            $query->whereDate('created_at', '<=', $to);
        }

        return view('admin.audit-log.index', [
            'logs' => $query->paginate(50)->withQueryString(),
            'admins' => User::orderBy('name')->get(['id', 'name']),
        ]);
    }
}
