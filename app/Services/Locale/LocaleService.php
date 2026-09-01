<?php

namespace App\Services\Locale;

use App\Models\User;
use App\Services\Base\BaseService;

class LocaleService extends BaseService
{
    public function setLocale(string $newLocale, ?User $user = null): void
    {
        $user?->update([
            'language' => $newLocale,
        ]);

        session()->put('language', $newLocale);
    }

    public static function generateLinkByLocale(string $currentLink, string $currentLocale, string $newLocale): string
    {
        $urlParsed = parse_url($currentLink);

        if ($urlParsed === false || ! isset($urlParsed['scheme'], $urlParsed['host'])) {
            return $currentLink;
        }

        $port = isset($urlParsed['port']) ? ':'.$urlParsed['port'] : '';
        $origin = $urlParsed['scheme'].'://'.$urlParsed['host'].$port;
        $segments = array_values(array_filter(
            explode('/', trim((string) ($urlParsed['path'] ?? ''), '/')),
            fn (string $segment) => $segment !== '',
        ));

        // The default language has no URL prefix. Strip any supported locale
        // that is already present before adding the requested one; otherwise
        // /ru became /ru/ru on the Russian homepage and every hreflang pair
        // pointed at the wrong document.
        if (isset($segments[0]) && in_array($segments[0], ['uk', 'ru'], true)) {
            array_shift($segments);
        }

        if ($newLocale !== config('app.fallback_locale')) {
            array_unshift($segments, $newLocale);
        }

        $path = $segments === [] ? '' : '/'.implode('/', $segments);
        $query = isset($urlParsed['query']) ? '?'.$urlParsed['query'] : '';

        return $origin.$path.$query;
    }

    /**
     * Canonical language alternatives for the current document.
     *
     * Search engines require every translated page to name itself and its
     * reciprocal version. Keeping this in the same URL normaliser as the
     * language switch prevents the two implementations drifting apart.
     *
     * @return array{uk-UA: string, ru-UA: string, x-default: string}
     */
    public static function alternateLinks(string $currentLink): array
    {
        $uk = self::generateLinkByLocale($currentLink, app()->getLocale(), 'uk');
        $ru = self::generateLinkByLocale($currentLink, app()->getLocale(), 'ru');

        return [
            'uk-UA' => $uk,
            'ru-UA' => $ru,
            'x-default' => $uk,
        ];
    }
}
