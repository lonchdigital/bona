@props([
    'productTypes',
    'options' => [],
    'contacts' => null,
    'overlay' => false,
])

@php
    // Both the hero overlay and the solid inner-page header are dark in the
    // approved storefront design, so they use the light logo consistently.
    $logoPath = $options['logoLight'] ?? $options['logoDark'] ?? null;
    $primaryStore = App\Support\Storefront\StoreLocations::from($contacts)->first();
    $phone = data_get($primaryStore, 'phone') ?: ($options['phoneOne'] ?? null);
    $workingHours = data_get($primaryStore, 'working_hours') ?: trans('base.working_hours');
    $hasMenuConfiguration = $productTypes->contains(fn ($productType) => $productType->catalogMenuConfiguration !== null);
    $navigationTypes = $hasMenuConfiguration
        ? $productTypes
            ->filter(fn ($productType) => $productType->catalogMenuConfiguration?->is_visible)
            ->sortBy(fn ($productType) => [
                $productType->catalogMenuConfiguration->sort_order,
                $productType->id,
            ])
            ->values()
        : $productTypes->values();
    $directTypes = $hasMenuConfiguration
        ? $productTypes
            ->filter(fn ($productType) => $productType->catalogMenuConfiguration?->show_in_header)
            ->sortBy(fn ($productType) => [
                $productType->catalogMenuConfiguration->header_order,
                $productType->id,
            ])
            ->values()
        : $productTypes->take(3);
    $promotedDirectCategorySlugs = ['dverni-rucky', 'door-handles'];
    $directLinks = $directTypes->map(function ($productType) use ($promotedDirectCategorySlugs) {
        $promotedCategory = $productType->categories
            ->first(fn ($category) => in_array($category->slug, $promotedDirectCategorySlugs, true));

        if ($promotedCategory) {
            return [
                'label' => $promotedCategory->name,
                'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                    'productTypeSlug' => $productType->slug,
                    'categorySlug' => $promotedCategory->slug,
                ]),
            ];
        }

        return [
            'label' => $productType->name,
            'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                'productTypeSlug' => $productType->slug,
            ]),
        ];
    });

    $mobileTypes = $productTypes->values();
    $mobileDoorTypeOrder = [
        'interior-doors' => 0,
        'hidden-doors' => 1,
        'entrance-doors' => 2,
        'visible-doors' => 3,
    ];
    $isDoorType = static function ($productType) use ($mobileDoorTypeOrder): bool {
        if (array_key_exists($productType->slug, $mobileDoorTypeOrder)) {
            return true;
        }

        return str_ends_with($productType->slug, '-doors')
            && ! str_contains($productType->slug, 'handle');
    };
    $mobileDoorTypes = $mobileTypes
        ->filter($isDoorType)
        ->sortBy(fn ($productType) => [
            $mobileDoorTypeOrder[$productType->slug] ?? 50,
            $productType->catalogMenuConfiguration?->sort_order ?? $productType->sort_order ?? 0,
            $productType->id,
        ])
        ->values();
    $mobileNonDoorTypes = $mobileTypes->reject($isDoorType)->values();
    $mobileSearchText = static function ($catalogItem): string {
        $translations = method_exists($catalogItem, 'getTranslations')
            ? $catalogItem->getTranslations('name')
            : [];

        return Illuminate\Support\Str::lower(Illuminate\Support\Str::ascii(implode(' ', [
            $catalogItem->slug ?? '',
            ...array_values($translations),
        ])));
    };
    $mobileFeaturedDefinitions = [
        ['wall-panel', 'stinov', 'stenov'],
        ['plintus', 'plinth', 'baseboard', 'skirting'],
        ['dverni-rucky', 'door-handles', 'door-handle'],
    ];
    $mobileCategoryCandidates = $mobileNonDoorTypes->flatMap(fn ($productType) => $productType->categories
        ->map(fn ($category) => ['productType' => $productType, 'category' => $category]));
    $mobileFeaturedLinks = collect();
    $mobileConsumedTypeIds = collect();

    foreach ($mobileFeaturedDefinitions as $patterns) {
        $matchingType = $mobileNonDoorTypes->first(
            fn ($productType) => Illuminate\Support\Str::contains($mobileSearchText($productType), $patterns),
        );

        if ($matchingType) {
            $mobileFeaturedLinks->push([
                'label' => $matchingType->name,
                'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                    'productTypeSlug' => $matchingType->slug,
                ]),
            ]);
            $mobileConsumedTypeIds->push($matchingType->id);
            continue;
        }

        $matchingCategory = $mobileCategoryCandidates->first(
            fn ($candidate) => Illuminate\Support\Str::contains($mobileSearchText($candidate['category']), $patterns),
        );

        if ($matchingCategory) {
            $mobileFeaturedLinks->push([
                'label' => $matchingCategory['category']->name,
                'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                    'productTypeSlug' => $matchingCategory['productType']->slug,
                    'categorySlug' => $matchingCategory['category']->slug,
                ]),
            ]);
            $mobileConsumedTypeIds->push($matchingCategory['productType']->id);
        }
    }

    $mobileCatalogLinks = $mobileFeaturedLinks
        ->concat($mobileNonDoorTypes
            ->reject(fn ($productType) => $mobileConsumedTypeIds->contains($productType->id))
            ->map(fn ($productType) => [
                'label' => $productType->name,
                'url' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                    'productTypeSlug' => $productType->slug,
                ]),
            ]))
        ->unique('url')
        ->values();
    $mobileDoorTypeNames = $mobileDoorTypes->pluck('name')->filter()->take(3)->implode(' · ');
@endphp

<div
    class="bona-site-header {{ $overlay ? 'bona-site-header--overlay' : 'bona-site-header--solid' }}"
    data-site-header
    @if($overlay) data-home-overlay-header @endif
>
    <div class="bona-topbar">
        <div class="bona-shell bona-topbar__inner">
            <nav class="bona-topbar__nav" aria-label="{{ trans('base.storefront_secondary_navigation') }}">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{ trans('base.about_us') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{ trans('base.delivery') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{ trans('base.blog') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{ trans('base.services') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">{{ trans('base.our_works') }}</a>
                <a class="bona-topbar__configurator" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.door-configurator.page') }}">{{ trans('configurator.nav_label') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{ trans('base.contacts') }}</a>
            </nav>
            <div class="bona-topbar__meta">
                <span>{{ $workingHours }}</span>
                @if($phone)
                    <a class="bona-topbar__phone" href="tel:{{ preg_replace('/[^+\d]/', '', $phone) }}">{{ $phone }}</a>
                @endif
                <a href="#dialog-call-measurer" data-lead-modal-open="dialog-call-measurer">{{ trans('base.call_measurer') }}</a>
            </div>
        </div>
    </div>

    <header class="bona-header">
        <div class="bona-shell bona-header__inner">
            <a class="bona-header__logo" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">
                @if($logoPath)
                    <img src="{{ '/storage/'.$logoPath }}" alt="Bona Doors" width="185" height="40" decoding="async">
                @else
                    <span>BONA</span><small>DOORS</small>
                @endif
            </a>

            <nav class="bona-mainnav" id="bona-mobile-navigation" aria-label="{{ trans('base.navigation') }}" data-main-navigation>
                <div class="bona-mainnav__catalog">
                    <button
                        class="bona-mainnav__catalog-toggle"
                        type="button"
                        aria-controls="bona-catalog-menu"
                        aria-expanded="false"
                        data-mega-toggle
                    >
                        <span>{{ trans('base.storefront_catalog') }}</span>
                        <svg width="11" height="7" viewBox="0 0 11 7" fill="none" aria-hidden="true"><path d="M1 1.5 5.5 5.6 10 1.5"></path></svg>
                    </button>
                    <x-store.mega-menu :product-types="$navigationTypes" />
                </div>

                @foreach($directLinks as $directLink)
                    <a class="bona-mainnav__direct" href="{{ $directLink['url'] }}">
                        {{ $directLink['label'] }}
                    </a>
                @endforeach

                <div class="bona-mobile-nav" data-mobile-navigation data-mobile-menu-level="root">
                    <div class="bona-mobile-nav__viewport">
                        <div class="bona-mobile-nav__track" data-mobile-menu-track>
                            <section
                                class="bona-mobile-nav__panel bona-mobile-nav__panel--root"
                                aria-label="{{ trans('base.storefront_mobile_catalog_navigation') }}"
                                data-mobile-menu-panel="root"
                            >
                                <p class="bona-mobile-nav__eyebrow">{{ trans('base.storefront_catalog_kicker') }}</p>

                                <div class="bona-mobile-nav__catalog-list">
                                    @if($mobileDoorTypes->isNotEmpty())
                                        <button
                                            class="bona-mobile-nav__entry bona-mobile-nav__entry--parent"
                                            type="button"
                                            aria-controls="bona-mobile-door-catalog"
                                            aria-expanded="false"
                                            data-mobile-menu-open="doors"
                                        >
                                            <span>
                                                <strong>{{ trans('base.storefront_catalog') }}</strong>
                                                @if($mobileDoorTypeNames !== '')
                                                    <small>{{ $mobileDoorTypeNames }}</small>
                                                @endif
                                            </span>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"></path></svg>
                                        </button>
                                    @endif

                                    @forelse($mobileCatalogLinks as $catalogLink)
                                        <a class="bona-mobile-nav__entry" href="{{ $catalogLink['url'] }}">
                                            <strong>{{ $catalogLink['label'] }}</strong>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"></path></svg>
                                        </a>
                                    @empty
                                        @if($mobileDoorTypes->isEmpty())
                                            <small class="bona-mobile-nav__empty">{{ trans('base.storefront_catalog_empty') }}</small>
                                        @endif
                                    @endforelse
                                </div>

                                <div class="bona-mobile-nav__utility">
                                    <x-store.configurator-menu-link />
                                    <a class="bona-mobile-nav__all" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">
                                        <span>{{ trans('base.all_products') }}</span>
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"></path></svg>
                                    </a>
                                </div>

                                <nav class="bona-mobile-nav__secondary" aria-label="{{ trans('base.storefront_secondary_navigation') }}">
                                    <p>{{ trans('base.storefront_mobile_information') }}</p>
                                    <div>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{ trans('base.about_us') }}</a>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{ trans('base.delivery') }}</a>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{ trans('base.blog') }}</a>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{ trans('base.services') }}</a>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">{{ trans('base.our_works') }}</a>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{ trans('base.contacts') }}</a>
                                    </div>
                                </nav>
                            </section>

                            <section
                                class="bona-mobile-nav__panel bona-mobile-nav__panel--doors"
                                id="bona-mobile-door-catalog"
                                aria-label="{{ trans('base.storefront_catalog') }}"
                                aria-hidden="true"
                                data-mobile-menu-panel="doors"
                                inert
                            >
                                <header class="bona-mobile-nav__level-header">
                                    <button type="button" data-mobile-menu-back>
                                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M19 12H6m5-5-5 5 5 5"></path></svg>
                                        <span>{{ trans('base.storefront_mobile_back') }}</span>
                                    </button>
                                    <p>{{ trans('base.storefront_catalog') }}</p>
                                </header>

                                <div class="bona-mobile-nav__door-list">
                                    @foreach($mobileDoorTypes as $productType)
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                                            <strong>{{ $productType->name }}</strong>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"></path></svg>
                                        </a>
                                    @endforeach
                                </div>

                                <a class="bona-mobile-nav__all bona-mobile-nav__all--sublevel" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">
                                    <span>{{ trans('base.all_products') }}</span>
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"></path></svg>
                                </a>
                            </section>
                        </div>
                    </div>
                </div>
            </nav>

            <x-store.search />

            <ul class="bona-header__actions header-main-others">
                <li class="bona-header__profile">
                    @auth
                        <x-user-profile-link :user="auth()->user()" />
                    @else
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in') }}" aria-label="{{ trans('base.user') }}">
                            <svg viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="6.5" r="3.2"></circle><path d="M3.8 17c.8-3.4 3.2-5 6.2-5s5.4 1.6 6.2 5"></path></svg>
                        </a>
                    @endauth
                </li>
                <li class="bona-header__wishlist wish-list-header-list">
                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.wishlist.private.page') }}" class="wishlist-link" aria-label="{{ trans('base.wish_list') }}">
                        <span class="art-main-wishlist-count d-none"></span>
                        <x-wish-heart />
                    </a>
                </li>
                <li class="bona-header__comparison">
                    <a
                        href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.comparison.page') }}"
                        aria-label="{{ trans('base.comparison') }}"
                        data-comparison-link
                    >
                        <span class="bona-header__comparison-count d-none" data-comparison-count>0</span>
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                            <path d="M5 7h14M16 4l3 3-3 3M19 17H5M8 14l-3 3 3 3"></path>
                        </svg>
                    </a>
                </li>
                <x-cart-window />
            </ul>

            <button
                class="bona-header__burger"
                type="button"
                aria-controls="bona-mobile-navigation"
                aria-expanded="false"
                aria-label="{{ trans('base.storefront_open_menu') }}"
                data-open-label="{{ trans('base.storefront_open_menu') }}"
                data-close-label="{{ trans('base.storefront_close_menu') }}"
                data-menu-toggle
            ><span></span><span></span><span></span></button>
        </div>
    </header>
</div>
