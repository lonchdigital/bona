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
        ['key' => 'telegram', 'label' => 'Telegram'],
        ['key' => 'viber', 'label' => 'Viber'],
        ['key' => 'facebook', 'label' => 'Facebook'],
    ])->filter(fn (array $social) => filled(data_get($options, $social['key'])));

    $stores = collect(['one', 'two', 'three'])->map(function (string $suffix) use ($contacts) {
        $city = data_get($contacts, "city_{$suffix}");
        $address = data_get($contacts, "address_{$suffix}");
        $phone = data_get($contacts, "phone_{$suffix}");
        $email = data_get($contacts, "email_{$suffix}");

        if (! filled($address)) {
            return null;
        }

        preg_match('/^(.*?)\s*\((.*?)\)$/u', (string) $address, $matches);

        return [
            'name' => trim($matches[1] ?? (string) $address),
            'address' => collect([$city, $matches[2] ?? null])->filter()->join(', '),
            'phone' => $phone,
            'phone_href' => filled($phone) ? preg_replace('/[^\d+]/', '', (string) $phone) : null,
            'email' => $email,
        ];
    })->filter();
@endphp

<footer class="bona-footer">
    <div class="bona-shell">
        <div class="bona-footer__grid">
            <div class="bona-footer__brand">
                @if($logoPath)
                    <img class="bona-footer__logo" src="{{ '/storage/'.$logoPath }}" alt="Bona Doors">
                @else
                    <img class="bona-footer__logo" src="{{ asset('assets/images/logo.png') }}" alt="Bona Doors">
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

            <nav aria-labelledby="footer-navigation-title">
                <h2 class="bona-footer__heading" id="footer-navigation-title">{{ trans('base.navigation') }}</h2>
                <ul class="bona-footer__links">
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{ trans('base.about_us') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{ trans('base.delivery') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{ trans('base.services') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">{{ trans('base.our_works') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{ trans('base.blog') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{ trans('base.contacts') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.faq.page') }}">{{ trans('base.faq') }}</a></li>
                </ul>
            </nav>

            <nav aria-labelledby="footer-categories-title">
                <h2 class="bona-footer__heading" id="footer-categories-title">{{ trans('base.footer_cat') }}</h2>
                <ul class="bona-footer__links">
                    @foreach($productTypes as $productType)
                        <li>
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">
                                {{ $productType->name }}
                            </a>
                        </li>
                    @endforeach
                    <li>
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', ['productTypeSlug' => 'aksessuar', 'categorySlug' => 'dverni-rucky']) }}">
                            {{ trans('shop.door_handles') }}
                        </a>
                    </li>
                </ul>
            </nav>

            @if($stores->isNotEmpty())
                <section aria-labelledby="footer-addresses-title">
                    <h2 class="bona-footer__heading" id="footer-addresses-title">{{ trans('base.footer_address') }}</h2>
                    <div class="bona-footer__addresses">
                        @foreach($stores as $store)
                            <address class="bona-footer__address">
                                <strong>{{ $store['name'] }}</strong>
                                <div class="bona-footer__address-row">
                                    <span>{{ trans('base.home_footer_address_label') }}</span>
                                    <p>{{ $store['address'] }}</p>
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
                                    <p>{{ trans('base.working_hours') }}</p>
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
                <span><img src="{{ Vite::asset('resources/img/payment/visa.svg') }}" alt="Visa"></span>
                <span><img src="{{ Vite::asset('resources/img/payment/mastercard.svg') }}" alt="Mastercard"></span>
            </span>
        </div>
    </div>
</footer>
