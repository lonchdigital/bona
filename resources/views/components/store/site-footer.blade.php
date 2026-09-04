@props([
    'productTypes' => collect(),
    'options' => [],
    'contacts' => null,
])

@php
    $locale = app()->getLocale();
    $footerText = data_get($options, "footerText.{$locale}") ?: trans('base.home_footer_about');
    $logoPath = data_get($options, 'logoLight');
    $socials = collect([
        ['key' => 'instagram', 'label' => 'Instagram'],
        ['key' => 'tiktok', 'label' => 'TikTok'],
        ['key' => 'telegram', 'label' => 'Telegram'],
        ['key' => 'viber', 'label' => 'Viber'],
        ['key' => 'facebook', 'label' => 'Facebook'],
    ])->filter(fn (array $social) => filled(data_get($options, $social['key'])));
    $stores = App\Support\Storefront\StoreLocations::from($contacts);
    $footerMenus = app(App\Services\CatalogMenu\CatalogMenuService::class)
        ->getStorefrontFooterMenus($options, $productTypes, $locale);
@endphp

<footer class="bona-footer">
    <div class="bona-shell">
        <div class="bona-footer__grid">
            <div class="bona-footer__brand">
                @if($logoPath)
                    <img class="bona-footer__logo" src="{{ '/storage/'.$logoPath }}" alt="Bona Doors" width="203" height="44" loading="lazy" decoding="async">
                @else
                    <span class="bona-footer__wordmark"><strong>BONA</strong><small>DOORS</small></span>
                @endif
                <p>{{ $footerText }}</p>

                @if($socials->isNotEmpty())
                    <div class="bona-footer__socials" aria-label="{{ trans('base.home_footer_socials') }}">
                        @foreach($socials as $social)
                            <a
                                href="{{ data_get($options, $social['key']) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ $social['label'] }}"
                            >
                                <span class="bona-footer__social-icon bona-footer__social-icon--{{ $social['key'] }}" aria-hidden="true"></span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <nav class="bona-footer__nav" aria-labelledby="footer-navigation-title">
                <h2 class="bona-footer__heading" id="footer-navigation-title">{{ trans('base.navigation') }}</h2>
                <ul class="bona-footer__links">
                    @foreach($footerMenus['navigation'] as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <nav class="bona-footer__nav" aria-labelledby="footer-categories-title">
                <h2 class="bona-footer__heading" id="footer-categories-title">{{ trans('base.footer_cat') }}</h2>
                <ul class="bona-footer__links">
                    @foreach($footerMenus['categories'] as $item)
                        <li><a href="{{ $item['url'] }}">{{ $item['label'] }}</a></li>
                    @endforeach
                </ul>
            </nav>

            @if($stores->isNotEmpty())
                <section class="bona-footer__stores" aria-labelledby="footer-addresses-title">
                    <h2 class="bona-footer__heading" id="footer-addresses-title">{{ trans('base.footer_address') }}</h2>
                    <div class="bona-footer__addresses">
                        @foreach($stores as $store)
                            <address class="bona-footer__address">
                                <strong>{{ $store['name'] }}</strong>
                                <div class="bona-footer__address-row">
                                    <span>{{ trans('base.home_footer_address_label') }}</span>
                                    <a
                                        class="bona-footer__address-link"
                                        href="{{ $store['map_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                    >
                                        {{ $store['address'] }}<span aria-hidden="true">↗</span>
                                    </a>
                                </div>
                                @if($store['phone'])
                                    <div class="bona-footer__address-row">
                                        <span>{{ trans('base.phone') }}</span>
                                        <a href="tel:{{ $store['phone_href'] }}">{{ $store['phone'] }}</a>
                                    </div>
                                @endif
                                @if($store['email'])
                                    <div class="bona-footer__address-row">
                                        <span>Email</span>
                                        <a href="mailto:{{ $store['email'] }}">{{ $store['email'] }}</a>
                                    </div>
                                @endif
                                <div class="bona-footer__address-row">
                                    <span>{{ trans('base.home_footer_hours_label') }}</span>
                                    <p>{{ $store['working_hours'] }}</p>
                                </div>
                            </address>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <div class="bona-footer__bottom">
            <p>BONA © {{ date('Y') }} {{ trans('base.all_rights_reserved') }}</p>
            <nav class="bona-footer__legal" aria-label="{{ trans('base.home_footer_legal') }}">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'exchange-and-return']) }}">{{ trans('base.exchange_and_return') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'polityka-konfidencinosti']) }}">{{ trans('base.policy') }}</a>
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">{{ trans('base.agreement') }}</a>
            </nav>
            <span class="bona-footer__payments" aria-label="Visa, Mastercard">
                <span><img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="Visa" width="40" height="20" loading="lazy"></span>
                <span><img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="Mastercard" width="40" height="20" loading="lazy"></span>
            </span>
        </div>
    </div>
</footer>
