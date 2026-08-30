<?php

namespace App\Helpers;

class MultiLangRoute
{
    public static function getMultiLangRoute(string $routeName, array $routeParams = []): string
    {
        $lang = app()->getLocale();

        if ($lang !== config('app.fallback_locale')) {
            return route(
                'localized.'.$routeName,
                ['lang' => $lang, ...$routeParams],
                false,
            );
        }

        return route($routeName, $routeParams, false);
    }
}
