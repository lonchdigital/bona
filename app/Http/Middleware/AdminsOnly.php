<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminsOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::user()->isAdmin()) {
            return response()->view('404', [], 404);
        }

        return $next($request);
    }
}
