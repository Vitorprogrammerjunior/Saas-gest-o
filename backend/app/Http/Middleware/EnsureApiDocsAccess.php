<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiDocsAccess
{
    /**
     * Allow access to API docs only in local environment
     * or when API_DOCS_ENABLED=true in .env
     *
     * In production, returns 404 so it looks like the route doesn't exist.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isLocal = app()->environment('local', 'development', 'testing');
        $isEnabled = config('app.api_docs_enabled', false);

        if (!$isLocal && !$isEnabled) {
            abort(404);
        }

        return $next($request);
    }
}
