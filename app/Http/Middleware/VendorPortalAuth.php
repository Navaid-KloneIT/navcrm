<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VendorPortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('vendor_portal_id')) {
            return redirect()->route('vendor-portal.login')
                ->with('error', 'Please log in to access the vendor portal.');
        }

        return $next($request);
    }
}
