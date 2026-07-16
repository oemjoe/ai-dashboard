<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DataConnection;
use App\Services\AI\AiProviderManager;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function public(): JsonResponse
    {
        return response()->json([
            'application' => config('app.name'),
            'status' => 'ok',
            'timestamp' => now()->toIso8601String(),
            'env' => config('app.env'),
        ]);
    }

    public function internal(AiProviderManager $aiProviderManager): JsonResponse
    {
        return response()->json([
            'application' => config('app.name'),
            'status' => 'ok',
            'connections' => DataConnection::query()->count(),
            'recent_audits' => AuditLog::query()->latest()->limit(5)->get(['action', 'status', 'created_at']),
            'providers' => $aiProviderManager->overview(),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
