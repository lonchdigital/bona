<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckLocale
{
    public function handle(Request $request, Closure $next): Response
    {
//        if ($request->method() !== 'POST') {
//            $userLang = config('app.fallback_locale');
//
//            if (auth()->user()) {
//                $userLang = auth()->user()->language;
//            } else if (session()->has('language')) {
//                $userLang = session()->get('language');
//            }
//
//            if (auth()->user() && app()->getLocale() !== $userLang) {
//                if($userLang !== config('app.fallback_locale')) {
//                    $newPath = $userLang . '/' . $request->path();
//                    return redirect()->to($newPath);
//                } else {
//                    //main page
//                    if (app()->getLocale() === $request->path()) {
//                        $newPath = '/';
//                    } else {
//                        $newPath = str_replace(app()->getLocale() . '/', '', $request->path());
//                    }
//                    return redirect()->to($newPath);
//                }
//            }
//        }

        return $next($request);
    }
}
