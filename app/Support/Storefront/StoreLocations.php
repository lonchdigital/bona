<?php

namespace App\Support\Storefront;

use Illuminate\Support\Collection;

final class StoreLocations
{
    /**
     * Convert the translated contact configuration into data shared by the
     * footer and the contacts page.
     *
     * @return Collection<int, array{
     *     key: string,
     *     name: string,
     *     address: string,
     *     map_url: string,
     *     phone: mixed,
     *     phone_href: ?string,
     *     email: mixed,
     *     working_hours: string,
     *     iframe_html: string
     * }>
     */
    public static function from(?object $contacts): Collection
    {
        if (! $contacts) {
            return collect();
        }

        return collect(['one', 'two', 'three'])
            ->map(function (string $suffix) use ($contacts): ?array {
                $city = trim((string) data_get($contacts, "city_{$suffix}"));
                $rawAddress = trim((string) data_get($contacts, "address_{$suffix}"));
                $phone = data_get($contacts, "phone_{$suffix}");
                $email = data_get($contacts, "email_{$suffix}");
                $workingHours = trim((string) data_get($contacts, "working_hours_{$suffix}"));

                if ($rawAddress === '') {
                    return null;
                }

                preg_match('/^(.*?)\s*\((.*?)\)$/u', $rawAddress, $matches);

                $hasNamedLocation = isset($matches[1], $matches[2]);
                $name = trim($hasNamedLocation ? $matches[1] : ($city ?: $rawAddress));
                $streetAddress = trim($hasNamedLocation ? $matches[2] : $rawAddress);
                $address = collect([$city, $streetAddress])
                    ->filter(fn (string $part): bool => $part !== '')
                    ->unique()
                    ->join(', ');
                $iframe = trim((string) data_get($contacts, "iframe_address_{$suffix}"));

                if ($iframe !== '' && ! preg_match('/<iframe\b[^>]*\btitle=/i', $iframe)) {
                    $title = e(trans('base.contact_map_title', ['store' => $name]));
                    $iframe = preg_replace('/<iframe\b/i', '<iframe title="'.$title.'"', $iframe, 1) ?? $iframe;
                }

                if ($iframe !== '' && ! preg_match('/<iframe\b[^>]*\bloading=/i', $iframe)) {
                    $iframe = preg_replace('/<iframe\b/i', '<iframe loading="lazy"', $iframe, 1) ?? $iframe;
                }

                return [
                    'key' => $suffix,
                    'name' => $name,
                    'address' => $address,
                    'map_url' => 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address),
                    'phone' => $phone,
                    'phone_href' => filled($phone) ? preg_replace('/[^\d+]/', '', (string) $phone) : null,
                    'email' => $email,
                    'working_hours' => $workingHours !== '' ? $workingHours : trans('base.working_hours'),
                    'iframe_html' => $iframe,
                ];
            })
            ->filter()
            ->values();
    }
}
