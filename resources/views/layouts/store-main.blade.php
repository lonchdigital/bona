<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Mobile Web-app fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    <link rel="preconnect" href="{{ request()->url() }}">
    <link rel="dns-prefetch" href="{{ request()->url() }}">

    <!-- Meta tags -->
    <meta name="author" content="">

    <meta property="og:locale" content="{{ app()->getLocale() }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:url" content="{{ request()->url() }}">
    {{-- Pages that own a meaningful picture override og_image; the favicon is
         only the last resort, and messengers show whichever tag comes first. --}}
    <meta property="og:image" content="@yield('og_image', Vite::asset('resources/img/favicon-32x32.png'))">
    <meta property="og:site_name" content="{{ mb_strtoupper(config('app.url')) }}">
    <link rel="apple-touch-icon" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">

    @php
        $currentUrl = url()->current();
        if (strpos($currentUrl, '?') !== false) {
            $currentUrl = strtok($currentUrl, '?');
        }
        $currentUrl = preg_replace('/\/filter\/.*/', '', $currentUrl);
    @endphp

    @hasSection('canonical')
        <link rel="canonical" href="@yield('canonical')">
    @else
        <link rel="canonical" href="{{ $currentUrl }}">
    @endif

    @if(Route::currentRouteName() === 'store.home')
        @if(Str::startsWith(request()->path(), 'ru'))
            @php $hreflangUrl = Str::after(request()->path(), 'ru'); @endphp
            <link hreflang="uk-UA" href="{{ url('') . $hreflangUrl }}" rel="alternate">
            <link hreflang="ru-UA" href="{{ url('/') .'/ru'. $hreflangUrl }}" rel="alternate">
            <link hreflang="x-default" href="{{ url('') . $hreflangUrl }}" rel="alternate">
        @else
            <link hreflang="uk-UA" href="{{ url('') }}" rel="alternate">
            <link hreflang="ru-UA" href="{{ url('') .'/ru' }}" rel="alternate">
            <link hreflang="x-default" href="{{ url('') }}" rel="alternate">
        @endif
    @elseif(Str::startsWith(request()->path(), 'ru/'))
        @php $hreflangUrl = Str::after(request()->path(), 'ru/'); @endphp
        <link hreflang="uk-UA" href="{{ url('/') .'/'. $hreflangUrl }}" rel="alternate">
        <link hreflang="ru-UA" href="{{ url('/') .'/ru/'. $hreflangUrl }}" rel="alternate">
        <link hreflang="x-default" href="{{ url('/') .'/'. $hreflangUrl }}" rel="alternate">
    @else
        <link hreflang="uk-UA" href="{{ url('/') .'/'. request()->path() }}" rel="alternate">
        <link hreflang="ru-UA" href="{{ url('/') .'/ru/'. request()->path() }}" rel="alternate">
        <link hreflang="x-default" href="{{ url('/') .'/'. request()->path() }}" rel="alternate">
    @endif

    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">

    @hasSection('title')
        @yield('title')
    @else
        <title>{{ config('app.name') . ' - ' . trans('base.site_title') }}</title>
    @endif
    @stack('head')

    @vite(['resources/scss/libs.scss'])

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
{{--    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">--}}
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/furniture-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/linear-icons.min.css') }}">

    {{--    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.min.css') }}">--}}
    {{--    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">--}}
    {{--    <link rel="stylesheet" href="{{ asset('assets/css/ion-range-slider.min.css') }}">--}}

    {{-- Plain stylesheet link: "as" belongs to rel="preload", and the onload
         only reassigned the rel it already had, so the pair did nothing. --}}
    <link rel="stylesheet" href="{{ Vite::asset('resources/scss/theme-additional.scss') }}">
    <noscript><link rel="stylesheet" href="{{ Vite::asset('resources/scss/theme-additional.scss') }}"></noscript>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Forum&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">


{{--    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">--}}
{{--    <link rel="preconnect" href="https://fonts.googleapis.com">--}}
{{--    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>--}}
{{--    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,600;1,400;1,700&family=Tenor+Sans&display=swap" rel="stylesheet">--}}


    @if(Route::currentRouteName() === 'store.home')

        <meta property="og:title" content="{{ config('app.name') . ' - ' . trans('base.site_title') }}">

        {{-- SearchAction is gone. It pointed at /search?q=, which answers 404:
             describing a search the site does not have is worse than
             describing none at all. --}}
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            '@id' => url('/') . '#website',
            'url' => url('/'),
            'name' => trans('base.organization'),
            'inLanguage' => app()->getLocale(),
            'publisher' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @endif

    {{-- The business itself, described once for every page so the two
         showrooms, the hours and the profiles all hang off one entity. --}}
    <script type="application/ld+json">{!! json_encode(
        app(\App\Services\Seo\OrganizationSchemaService::class)->build($applicationGlobalOptions ?? []),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
    ) !!}</script>

<!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-P9KHGB8T');</script>
    <!-- End Google Tag Manager -->

</head>

<body>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P9KHGB8T" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


{{--<div class="page-loader"></div>--}}
{{--@if (!session()->has('language_popup_shown'))--}}

{{--@dd(app()->getLocale())--}}

<a href="" id="art-language-popup-button" data-fancybox data-src="#art-language-popup" style="display: none;"></a>
<div id="art-language-popup" class="art-popup-language" style="display: none;">

    <div class="art-measurer-form-wrapper">
        <div class="container">

            <div class="row">
                <div class="col-12 text-center">

                    <header class="art-light">
                        <div class="text-center">
                            <div class="title h2">{{ trans('base.choose_language') }}</div>
                            <div class="subtitle font-two">
                                <p class="art-form-description">{{ trans('base.choose_language_to_display_website') }}</p>
                            </div>
                        </div>
                    </header>

                    <div class="art-fields-row">
                        @if(app()->getLocale() == 'uk')
                            <a class="btn btn-empty" data-fancybox-close href="">Українська</a>
                        @else
                            <a class="btn btn-empty" href="{{ $locationService->generateLinkByLocale(url()->current(), app()->getLocale(), 'uk') }}">Українська</a>
                        @endif

                        @if(app()->getLocale() == 'ru')
                            <a class="btn btn-empty" data-fancybox-close href="">Русский</a>
                        @else
                            <a class="btn btn-empty" href="{{ $locationService->generateLinkByLocale(url()->current(), app()->getLocale(), 'ru') }}">Русский</a>
                        @endif
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>



<a href="" id="user-choose-doors-success" data-fancybox data-src="#art-user-choose-doors" style="display: none">Launch Dialog</a>
<div id="art-user-choose-doors" style="display: none">
    <div class="">
        <div class="h2">{{ trans('base.form_sent_success') }}</div>
    </div>
</div>

<div class="wrapper">

    <x-store.site-header
        :product-types="$productTypes"
        :options="$applicationGlobalOptions"
        :overlay="request()->routeIs('store.home')"
    />
    <x-store.call-measurer-modal />


@yield('content')


@hasSection('noFooter')
@else
    <x-store.site-footer
        :product-types="$productTypes"
        :options="$applicationGlobalOptions"
        :contacts="$contactsFooter"
    />
@endif

</div>

{{-- static scripts start --}}
<script src="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('static-data.script') }}?lang={{ app()->getLocale() }}" defer></script>
{{-- static scripts end --}}

{{-- dinamic scripts start --}}
<script>
    const page = '{{ request()->route() ? request()->route()->getName() : '' }}';
    const is_auth = {{ auth()->user() ? 'true' : 'false' }};
    const locale = '{{ app()->getLocale() }}';
    const csrf = '{{ csrf_token() }}';
    const count_of_products_in_cart = {{ $countOfProductInCart }};
</script>
@stack('dynamic_scripts')
{{-- dinamic scripts end --}}

@vite('resources/js/store/app.js')

</body>
</html>
