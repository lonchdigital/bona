<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Mobile Web-app fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    @php
        $isHomePage = request()->routeIs('store.home', 'localized.store.home');
        $currentUrl = url()->current();
        $currentUrl = preg_replace('/\/filter\/.*/', '', $currentUrl);
        $canonicalUrl = trim($__env->yieldContent('canonical')) ?: $currentUrl;
        $canonicalUrl = Illuminate\Support\Str::startsWith($canonicalUrl, ['http://', 'https://'])
            ? $canonicalUrl
            : url($canonicalUrl);
        $alternateLinks = App\Services\Locale\LocaleService::alternateLinks($canonicalUrl);
        $pageTitle = trim($__env->yieldContent('seo_title'))
            ?: config('app.name').' - '.trans('base.site_title');
        $pageDescription = trim($__env->yieldContent('meta_description'));
        $pageKeywords = trim($__env->yieldContent('meta_keywords'));
        $ogTitle = trim($__env->yieldContent('og_title')) ?: $pageTitle;
        $ogDescription = trim($__env->yieldContent('og_description')) ?: $pageDescription;
        $socialImageAlt = trim($__env->yieldContent('og_image_alt')) ?: $pageTitle;
        $twitterTitle = trim($__env->yieldContent('twitter_title')) ?: $pageTitle;
        $twitterDescription = trim($__env->yieldContent('twitter_description')) ?: $pageDescription;
        $defaultSocialImage = filled($applicationGlobalOptions['logoLight'] ?? null)
            ? url('/storage/'.$applicationGlobalOptions['logoLight'])
            : Vite::asset('resources/img/favicon-32x32.png');
        $socialImage = trim($__env->yieldContent('og_image')) ?: $defaultSocialImage;
        $socialImage = Illuminate\Support\Str::startsWith($socialImage, ['http://', 'https://'])
            ? $socialImage
            : url($socialImage);
        $ogLocale = app()->getLocale() === 'ru' ? 'ru_UA' : 'uk_UA';
        $ogAlternateLocale = app()->getLocale() === 'ru' ? 'uk_UA' : 'ru_UA';
    @endphp

    @hasSection('seo_title')
        <title>{{ $pageTitle }}</title>
        <meta name="title" content="{{ $pageTitle }}">
    @else
        @hasSection('title')
            @yield('title')
        @else
            <title>{{ $pageTitle }}</title>
        @endif
    @endif

    @if($pageDescription !== '')
        <meta name="description" content="{{ $pageDescription }}">
    @endif
    @if($pageKeywords !== '')
        <meta name="keywords" content="{{ $pageKeywords }}">
    @endif
    @if($isHomePage)
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    @endif

    <link rel="canonical" href="{{ $canonicalUrl }}">
    @foreach($alternateLinks as $hreflang => $href)
        <link rel="alternate" hreflang="{{ $hreflang }}" href="{{ $href }}">
    @endforeach

    <meta property="og:locale" content="{{ $ogLocale }}">
    <meta property="og:locale:alternate" content="{{ $ogAlternateLocale }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $ogTitle }}">
    @if($ogDescription !== '')
        <meta property="og:description" content="{{ $ogDescription }}">
    @endif
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:image" content="{{ $socialImage }}">
    <meta property="og:image:secure_url" content="{{ $socialImage }}">
    <meta property="og:image:alt" content="{{ $socialImageAlt }}">
    <meta property="og:site_name" content="Bona Doors">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $twitterTitle }}">
    @if($twitterDescription !== '')
        <meta name="twitter:description" content="{{ $twitterDescription }}">
    @endif
    <meta name="twitter:image" content="{{ $socialImage }}">
    <meta name="twitter:image:alt" content="{{ $socialImageAlt }}">

    <meta name="msapplication-TileColor" content="#231f1b">
    <meta name="theme-color" content="#231f1b">
    <link rel="apple-touch-icon" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">

    @stack('head')

    {{-- The current storefront has its own compact critical bundle. Historical
         widgets still receive Bootstrap and the legacy theme asynchronously. --}}
    <link rel="stylesheet" href="{{ Vite::asset('resources/scss/storefront.scss') }}">

    @unless($isHomePage)
        <link rel="stylesheet" href="{{ Vite::asset('resources/scss/libs.scss') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/furniture-icons.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ asset('assets/css/linear-icons.min.css') }}" media="print" onload="this.media='all'">
        <link rel="stylesheet" href="{{ Vite::asset('resources/scss/theme-additional.scss') }}" media="print" onload="this.media='all'">
        <noscript>
            <link rel="stylesheet" href="{{ Vite::asset('resources/scss/libs.scss') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/furniture-icons.min.css') }}">
            <link rel="stylesheet" href="{{ asset('assets/css/linear-icons.min.css') }}">
            <link rel="stylesheet" href="{{ Vite::asset('resources/scss/theme-additional.scss') }}">
        </noscript>
    @endunless

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/forum/v19/6aey4Ky-Vb8Ew8IVOpI43XnSBTM.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/forum/v19/6aey4Ky-Vb8Ew8IROpI43XnS.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/manrope/v20/xn7gYHE41ni1AdIRggOxSvfedN62Zw.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="https://fonts.gstatic.com/s/manrope/v20/xn7gYHE41ni1AdIRggexSvfedN4.woff2" as="font" type="font/woff2" crossorigin>

    {{-- This face ships as WOFF2 but the vendor stylesheet omitted the display
         strategy, leaving icon text invisible during a slow font download. --}}
    @unless($isHomePage)
        <style>
            @font-face {
                font-family: 'LinearIcons';
                src: url('{{ asset('assets/fonts/linearIcons.woff2') }}?w118d') format('woff2');
                font-style: normal;
                font-weight: 400;
                font-display: swap;
            }
        </style>
    @endunless

    {{-- The business itself, described once for every page so the two
         showrooms, the hours and the profiles all hang off one entity. --}}
    <script type="application/ld+json">{!! json_encode(
        app(\App\Services\Seo\OrganizationSchemaService::class)->build($applicationGlobalOptions ?? []),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG
    ) !!}</script>
    @stack('structured_data')

<!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
                new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-P9KHGB8T');</script>
    <!-- End Google Tag Manager -->

</head>

<body class="@yield('body_class')">
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-P9KHGB8T" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->


<a href="" id="user-choose-doors-success" data-fancybox data-src="#art-user-choose-doors" style="display: none">Launch Dialog</a>
<div id="art-user-choose-doors" style="display: none">
    <div class="">
        <div class="h2">{{ trans('base.form_sent_success') }}</div>
    </div>
</div>

<a class="bona-skip-link" href="#main-content">{{ app()->getLocale() === 'ru' ? 'Перейти к содержимому' : 'Перейти до вмісту' }}</a>

<div class="wrapper">

    <x-store.site-header
        :product-types="$productTypes"
        :options="$applicationGlobalOptions"
        :overlay="request()->routeIs('store.home', 'localized.store.home')"
    />
    <x-store.call-measurer-modal />


<main id="main-content">
    @yield('content')
</main>


@hasSection('noFooter')
@else
    <x-store.site-footer
        :product-types="$productTypes"
        :options="$applicationGlobalOptions"
        :contacts="$contactsFooter"
    />
@endif

</div>

<x-store.mobile-bottom-navigation />
<x-store.comparison-dock />

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
