<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Contracts\View\View;

class SystemHealthController extends Controller
{
    public function index(DashboardDataService $dashboardDataService): View
    {
        return view('admin.system-health.index', $dashboardDataService->adminHealth());
    }
}
