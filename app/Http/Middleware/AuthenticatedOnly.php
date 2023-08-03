<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticatedOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::user()) {
            if ($request->ajax()) {
                return \response(json_encode(['error' => 'Access denied!']), 403);
            }
            return redirect()->route('auth.sign-in');
        }

        return $next($request);
    }
}
