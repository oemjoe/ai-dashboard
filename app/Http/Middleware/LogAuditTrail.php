<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Services\Security\MaskingService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogAuditTrail
{
    public function __construct(private readonly MaskingService $maskingService)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);
        $response = $next($request);

        if (in_array($request->route()?->getName(), ['health.public', 'api.health.public'], true)) {
            return $response;
        }

        $user = $request->user();

        AuditLog::query()->create([
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'role_names' => $user?->roles->pluck('name')->values()->all(),
            'company_code' => $user?->preferred_company_code,
            'module_slug' => $request->route('module'),
            'action' => $request->route()?->getName() ?? $request->method().' '.$request->path(),
            'question' => $this->maskingService->sanitizeFreeText($request->input('question')),
            'status' => $response->getStatusCode() >= 400 ? 'error' : 'success',
            'response_time_ms' => (int) round((microtime(true) - $startedAt) * 1000),
            'route_name' => $request->route()?->getName(),
            'method' => $request->method(),
            'path' => $request->path(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'trace_id' => $request->attributes->get('trace_id'),
            'context' => [
                'filters' => $request->except(['password', 'password_confirmation', 'token']),
            ],
        ]);

        return $response;
    }
}
