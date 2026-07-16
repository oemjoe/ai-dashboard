<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class ApplyRequestContext
{
    public function handle(Request $request, Closure $next): Response
    {
        date_default_timezone_set(config('app.timezone'));
        $request->attributes->set('trace_id', (string) Str::uuid());

        return $next($request);
    }
}
