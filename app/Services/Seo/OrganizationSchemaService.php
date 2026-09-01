<?php

namespace App\Services\Seo;

use App\Models\ContactConfig;

/**
 * Builds the Organization / LocalBusiness description of the business.
 *
 * The markup used to be written out as a JSON string inside the layout, which
 * is how it ended up with a capitalised "Name", a relative url, an empty
 * sameAs entry and a country spelled out in Ukrainian. Assembled as arrays and
 * encoded, none of that can happen unnoticed.
 */
class OrganizationSchemaService
{
    public function organizationId(): string
    {
        return url('/').'#organization';
    }

    public function build(array $applicationGlobalOptions = []): array
    {
        $logo = $this->logoUrl($applicationGlobalOptions);
        $showrooms = $this->showrooms($logo);

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $this->organizationId(),
            'name' => config('organization.name'),
            'alternateName' => 'Bona Doors',
            'url' => url('/'),
            'logo' => $logo ? [
                '@type' => 'ImageObject',
                'url' => $logo,
                'contentUrl' => $logo,
            ] : null,
            'image' => $logo,
            'foundingDate' => config('organization.founding_date'),
            'telephone' => $this->primaryTelephone(),
            'email' => $this->email(),
            'areaServed' => array_map(
                fn (string $area) => ['@type' => 'AdministrativeArea', 'name' => $area],
                (array) config('organization.area_served', [])
            ),
            'sameAs' => (array) config('organization.same_as', []),
            'subOrganization' => array_map(
                fn (array $showroom) => ['@id' => $showroom['@id']],
                $showrooms,
            ),
            'contactPoint' => array_filter([
                '@type' => 'ContactPoint',
                'contactType' => 'sales',
                'telephone' => $this->primaryTelephone(),
                'email' => $this->email(),
                'areaServed' => 'UA',
                'availableLanguage' => ['uk', 'ru'],
            ]),
        ], fn ($value) => $value !== null && $value !== [] && $value !== '');

        return [
            '@context' => 'https://schema.org',
            '@graph' => [$organization, ...$showrooms],
        ];
    }

    /**
     * Each showroom described in its own right, so both can be placed on a map
     * rather than the business having one vague address.
     */
    private function showrooms(?string $logo): array
    {
        $showrooms = [];

        foreach ((array) config('organization.showrooms', []) as $index => $showroom) {
            $showrooms[] = array_filter([
                '@type' => 'HomeGoodsStore',
                '@id' => url('/').'#showroom-'.($index + 1),
                'name' => $showroom['name'] ?? null,
                'parentOrganization' => ['@id' => $this->organizationId()],
                'url' => url('/'),
                'telephone' => $showroom['telephone'] ?? null,
                'image' => $logo,
                'address' => $this->address($showroom),
                'geo' => isset($showroom['latitude'], $showroom['longitude']) ? [
                    '@type' => 'GeoCoordinates',
                    'latitude' => $showroom['latitude'],
                    'longitude' => $showroom['longitude'],
                ] : null,
                'hasMap' => config('organization.map_url'),
                'openingHoursSpecification' => $this->openingHours(),
                'priceRange' => config('organization.price_range'),
                'currenciesAccepted' => config('organization.currencies_accepted'),
            ], fn ($value) => $value !== null && $value !== [] && $value !== '');
        }

        return $showrooms;
    }

    private function address(array $showroom): ?array
    {
        if (! $showroom) {
            return null;
        }

        return array_filter([
            '@type' => 'PostalAddress',
            'streetAddress' => $showroom['street'] ?? null,
            'addressLocality' => $showroom['locality'] ?? null,
            'addressRegion' => $showroom['region'] ?? null,
            'postalCode' => $showroom['postal_code'] ?? null,
            // A country code, not the country written out in words.
            'addressCountry' => $showroom['country'] ?? null,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function openingHours(): array
    {
        $hours = (array) config('organization.opening_hours', []);

        if (! isset($hours['days'], $hours['opens'], $hours['closes'])) {
            return [];
        }

        return [[
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => $hours['days'],
            'opens' => $hours['opens'],
            'closes' => $hours['closes'],
        ]];
    }

    private function primaryTelephone(): ?string
    {
        return config('organization.showrooms.0.telephone');
    }

    private function email(): ?string
    {
        $email = ContactConfig::first()?->email_one;

        return is_string($email) && $email !== '' ? $email : null;
    }

    private function logoUrl(array $applicationGlobalOptions): ?string
    {
        $logo = $applicationGlobalOptions['logoLight'] ?? null;

        return $logo ? url('/storage/'.$logo) : null;
    }
}
