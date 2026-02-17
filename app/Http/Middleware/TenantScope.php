<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantScope
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = auth()->user();

        if (!$user->tenant_id) {
            return response()->json(['message' => 'No tenant associated'], 403);
        }

        if (!$user->tenant || !$user->tenant->is_active) {
            return response()->json(['message' => 'Tenant is inactive'], 403);
        }

        return $next($request);
    }
}
