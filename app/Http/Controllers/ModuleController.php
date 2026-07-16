<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardDataService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    public function show(Request $request, string $module, DashboardDataService $dashboardDataService): View
    {
        return view('modules.show', $dashboardDataService->modulePayload(
            slug: $module,
            user: $request->user(),
            filters: $request->only(['company', 'period', 'date_from', 'date_to', 'division', 'branch', 'location']),
        ));
    }
}
