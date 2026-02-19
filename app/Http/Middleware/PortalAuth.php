<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('portal_contact_id')) {
            return redirect()->route('portal.login')
                ->with('error', 'Please log in to access the customer portal.');
        }

        return $next($request);
    }
}
