<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    /**
     * Audit log listing.
     */
    public function index(Request $request)
    {
        $query = AuditLog::with('user');

        // Search by action
        if ($action = $request->input('action')) {
            $query->byAction($action);
        }

        // Filter by user
        if ($userId = $request->input('user_id')) {
            $query->where('user_id', $userId);
        }

        // Filter by target type
        if ($targetType = $request->input('target_type')) {
            $query->where('target_type', $targetType);
        }

        // Date range
        if ($from = $request->input('from')) {
            $query->where('created_at', '>=', $from);
        }
        if ($to = $request->input('to')) {
            $query->where('created_at', '<=', $to . ' 23:59:59');
        }

        $logs = $query->latest('created_at')->paginate(30)->withQueryString();

        // Get unique actions for filter dropdown
        $actions = AuditLog::selectRaw('DISTINCT action')->pluck('action');

        // Get unique target types
        $targetTypes = AuditLog::selectRaw('DISTINCT target_type')
            ->whereNotNull('target_type')
            ->pluck('target_type');

        return view('admin.audit-log.index', compact('logs', 'actions', 'targetTypes'));
    }

    /**
     * Show audit log detail.
     */
    public function show(AuditLog $auditLog)
    {
        $auditLog->load('user');

        return view('admin.audit-log.show', compact('auditLog'));
    }
}
