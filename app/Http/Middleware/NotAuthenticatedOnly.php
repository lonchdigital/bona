<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class NotAuthenticatedOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user()) {
            return redirect()->route('store.home');
        }

        return $next($request);
    }
}
