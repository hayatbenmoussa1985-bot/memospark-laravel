<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Report;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * List reports with filter.
     */
    public function index(Request $request)
    {
        $query = Report::with(['reporter']);

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        } else {
            // Default: show pending first
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END");
        }

        // Filter by type
        if ($type = $request->input('type')) {
            $query->where('reportable_type', $type);
        }

        $reports = $query->latest()->paginate(20)->withQueryString();

        $pendingCount = Report::where('status', 'pending')->count();

        return view('admin.reports.index', compact('reports', 'pendingCount'));
    }

    /**
     * Show report detail.
     */
    public function show(Report $report)
    {
        $report->load(['reporter', 'reviewer']);

        // Load the reported item
        $reportedItem = $report->reportable();

        return view('admin.reports.show', compact('report', 'reportedItem'));
    }

    /**
     * Resolve a report.
     */
    public function resolve(Request $request, Report $report)
    {
        $validated = $request->validate([
            'resolution_note' => ['required', 'string', 'max:1000'],
        ]);

        $report->resolve(auth()->id(), $validated['resolution_note']);

        AuditLog::record(
            action: 'report_resolved',
            targetType: 'report',
            targetId: $report->id,
            newValues: ['resolution_note' => $validated['resolution_note']],
        );

        return redirect()
            ->route('admin.reports.index')
            ->with('success', 'Report resolved.');
    }

    /**
     * Dismiss a report.
     */
    public function dismiss(Request $request, Report $report)
    {
        $validated = $request->validate([
            'resolution_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $report->dismiss(auth()->id(), $validated['resolution_note'] ?? null);

        AuditLog::record(
            action: 'report_dismissed',
            targetType: 'report',
            targetId: $report->id,
        );

        return redirect()
            ->route('admin.reports.index')
            ->with('success', 'Report dismissed.');
    }
}
