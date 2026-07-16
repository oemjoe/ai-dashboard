<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $moduleSlug = (string) $request->route('module');
        $module = collect(config('ai_dashboard.modules'))
            ->firstWhere('slug', $moduleSlug);

        abort_unless($module, 404);

        $user = $request->user();
        abort_unless($user, 403);

        if (! $user->hasRole('Super Admin') && ! $user->can($module['permission'])) {
            abort(403, 'You do not have access to this module.');
        }

        if (
            $request->filled('company')
            && $user->preferred_company_code
            && ! $user->hasAnyRole(['Super Admin', 'IT Admin'])
            && strtoupper((string) $request->string('company')) !== strtoupper((string) $user->preferred_company_code)
        ) {
            abort(403, 'Cross-company access is not permitted for this account.');
        }

        return $next($request);
    }
}
