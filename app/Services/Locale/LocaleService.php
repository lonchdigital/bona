<?php

namespace App\Services\Locale;

use App\Models\User;
use App\Services\Base\BaseService;

class LocaleService extends BaseService
{
    public function setLocale(string $newLocale, ?User $user = null): void
    {
        $user?->update([
            'language' => $newLocale
        ]);

        session()->put('language', $newLocale);
    }

    public static function generateLinkByLocale(string $currentLink, string $currentLocale, string $newLocale): string
    {
        $urlParsed = parse_url($currentLink);
        $port = isset($urlParsed['port']) ? ':' . $urlParsed['port'] : '';
        $newUrl = $urlParsed['scheme'] . '://' . $urlParsed['host'] . $port;
        $currentPath = isset($urlParsed['path']) ? $urlParsed['path'] : '';
        $path = '';

        if ($currentLocale === config('app.fallback_locale')) {
            $path = '/' . $newLocale . $currentPath;
        } else {
            //has lang prefix
            $path = str_replace('/' . $currentLocale . '/', '/', $currentPath);
            if ($newLocale !== config('app.fallback_locale')) {
                $path = '/' . $newLocale . $path;
            }

        }

        return $newUrl . $path;
    }
}
