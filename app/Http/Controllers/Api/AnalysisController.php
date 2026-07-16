<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardDataService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalysisController extends Controller
{
    public function show(Request $request, string $module, DashboardDataService $dashboardDataService): JsonResponse
    {
        return response()->json(
            $dashboardDataService->modulePayload($module, $request->user(), $request->all())
        );
    }

    public function dashboard(Request $request, DashboardDataService $dashboardDataService): JsonResponse
    {
        return response()->json(
            $dashboardDataService->overview($request->user())
        );
    }
}
