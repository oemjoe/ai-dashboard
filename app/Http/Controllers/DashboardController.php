<?php

namespace App\Http\Controllers;

use App\Services\Dashboard\DashboardDataService;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function __invoke(DashboardDataService $dashboardDataService): View
    {
        return view('dashboard', $dashboardDataService->overview(auth()->user()));
    }
}
