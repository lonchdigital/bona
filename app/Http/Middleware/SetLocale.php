<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale($request->segment(1));

        \URL::defaults(['lang' => $request->segment(1)]);

        $request->route()->forgetParameter('lang');

        if (auth()->user()) {
            auth()->user()->update([
                'language' => $request->segment(1)
            ]);
        }

        return $next($request);
    }
}
