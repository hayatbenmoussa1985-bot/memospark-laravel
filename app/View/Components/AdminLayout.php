<?php

namespace App\View\Components;

use App\Models\Report;
use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        // Share pending reports count for sidebar badge
        $pendingReports = Report::where('status', 'pending')->count();

        return view('layouts.admin', [
            'pendingReports' => $pendingReports,
        ]);
    }
}
