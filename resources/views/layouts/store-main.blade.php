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

    <link rel="canonical" href="{{ $currentUrl }}">

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
    <!-- ================== Footer  ================== -->
        <footer class="art-site-footer">

            <div class="footer-content">

                <div class="container">
                    <div class="art-flex-row">

                        <div class="footer-content-left">
                            @if(array_key_exists('logoLight', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['logoLight']))
                                <img src="{{ '/storage/' . $applicationGlobalOptions['logoLight'] }}" alt="Logo">
                            @endif

                            @if(array_key_exists('footerText', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['footerText']))
                                <p class="art-footer-description">{{ $applicationGlobalOptions['footerText'][app()->getLocale()] }}</p>
                            @endif

                            <div class="art-footer-additional">
                                <ul class="art-footer-social">
                                    @if(array_key_exists('instagram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['instagram']))
                                        <li class="instagram-item">
                                            <a href="{{ $applicationGlobalOptions['instagram'] }}" target="_blank">
                                                <svg width="19" height="19" viewBox="0 0 19 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M11.3806 11.9606C11.3806 11.9606 11.5305 11.8107 11.8303 11.5109C12.1301 11.2111 12.28 10.6374 12.28 9.79C12.28 8.94255 11.9802 8.21902 11.3806 7.61941C10.781 7.0198 10.0574 6.72 9.21 6.72C8.36255 6.72 7.63902 7.0198 7.03941 7.61941C6.4398 8.21902 6.14 8.94255 6.14 9.79C6.14 10.6374 6.4398 11.361 7.03941 11.9606C7.63902 12.5602 8.36255 12.86 9.21 12.86C10.0574 12.86 10.781 12.5602 11.3806 11.9606ZM12.5558 6.44418C12.5558 6.44418 12.7857 6.67403 13.2454 7.13373C13.7051 7.59343 13.9349 8.47885 13.9349 9.79C13.9349 11.1011 13.4752 12.2164 12.5558 13.1358C11.6364 14.0552 10.5211 14.5149 9.21 14.5149C7.89885 14.5149 6.78358 14.0552 5.86418 13.1358C4.94478 12.2164 4.48508 11.1011 4.48508 9.79C4.48508 8.47885 4.94478 7.36358 5.86418 6.44418C6.78358 5.52478 7.89885 5.06508 9.21 5.06508C10.5211 5.06508 11.6364 5.52478 12.5558 6.44418ZM14.9063 4.09371C14.9063 4.09371 14.9603 4.14768 15.0682 4.25561C15.1761 4.36354 15.2301 4.5694 15.2301 4.8732C15.2301 5.17701 15.1221 5.43684 14.9063 5.6527C14.6904 5.86855 14.4306 5.97648 14.1268 5.97648C13.823 5.97648 13.5632 5.86855 13.3473 5.6527C13.1314 5.43684 13.0235 5.17701 13.0235 4.8732C13.0235 4.5694 13.1314 4.30957 13.3473 4.09371C13.5632 3.87785 13.823 3.76992 14.1268 3.76992C14.4306 3.76992 14.6904 3.87785 14.9063 4.09371ZM10.1274 2.22893C10.1274 2.22893 9.98849 2.22993 9.71067 2.23192C9.43285 2.23392 9.26596 2.23492 9.21 2.23492C9.15404 2.23492 8.84824 2.23292 8.2926 2.22893C7.73696 2.22493 7.31523 2.22493 7.02742 2.22893C6.73961 2.23292 6.35386 2.24492 5.87018 2.2649C5.38649 2.28489 4.97476 2.32486 4.63498 2.38482C4.2952 2.44479 4.00939 2.51874 3.77754 2.60668C3.3778 2.76658 3.02603 2.99842 2.72223 3.30223C2.41842 3.60603 2.18658 3.9578 2.02668 4.35754C1.93874 4.58939 1.86479 4.8752 1.80482 5.21498C1.74486 5.55476 1.70489 5.96649 1.6849 6.45018C1.66492 6.93386 1.65292 7.31961 1.64893 7.60742C1.64493 7.89523 1.64493 8.31696 1.64893 8.8726C1.65292 9.42824 1.65492 9.73404 1.65492 9.79C1.65492 9.84596 1.65292 10.1518 1.64893 10.7074C1.64493 11.263 1.64493 11.6848 1.64893 11.9726C1.65292 12.2604 1.66492 12.6461 1.6849 13.1298C1.70489 13.6135 1.74486 14.0252 1.80482 14.365C1.86479 14.7048 1.93874 14.9906 2.02668 15.2225C2.18658 15.6222 2.41842 15.974 2.72223 16.2778C3.02603 16.5816 3.3778 16.8134 3.77754 16.9733C4.00939 17.0613 4.2952 17.1352 4.63498 17.1952C4.97476 17.2551 5.38649 17.2951 5.87018 17.3151C6.35386 17.3351 6.73961 17.3471 7.02742 17.3511C7.31523 17.3551 7.73696 17.3551 8.2926 17.3511C8.84824 17.3471 9.15404 17.3451 9.21 17.3451C9.26596 17.3451 9.57176 17.3471 10.1274 17.3511C10.683 17.3551 11.1048 17.3551 11.3926 17.3511C11.6804 17.3471 12.0661 17.3351 12.5498 17.3151C13.0335 17.2951 13.4452 17.2551 13.785 17.1952C14.1248 17.1352 14.4106 17.0613 14.6425 16.9733C15.0422 16.8134 15.394 16.5816 15.6978 16.2778C16.0016 15.974 16.2334 15.6222 16.3933 15.2225C16.4813 14.9906 16.5552 14.7048 16.6152 14.365C16.6751 14.0252 16.7151 13.6135 16.7351 13.1298C16.7551 12.6461 16.7671 12.2604 16.7711 11.9726C16.7751 11.6848 16.7751 11.263 16.7711 10.7074C16.7671 10.1518 16.7651 9.84596 16.7651 9.79C16.7651 9.73404 16.7671 9.42824 16.7711 8.8726C16.7751 8.31696 16.7751 7.89523 16.7711 7.60742C16.7671 7.31961 16.7551 6.93386 16.7351 6.45018C16.7151 5.96649 16.6751 5.55476 16.6152 5.21498C16.5552 4.8752 16.4813 4.58939 16.3933 4.35754C16.2334 3.9578 16.0016 3.60603 15.6978 3.30223C15.394 2.99842 15.0422 2.76658 14.6425 2.60668C14.4106 2.51874 14.1248 2.44479 13.785 2.38482C13.4452 2.32486 13.0335 2.28489 12.5498 2.2649C12.0661 2.24492 11.6804 2.23292 11.3926 2.22893C11.1048 2.22493 10.683 2.22493 10.1274 2.22893ZM18.36 5.98848C18.4 6.69202 18.42 7.95919 18.42 9.79C18.42 11.6208 18.4 12.888 18.36 13.5915C18.2801 15.2544 17.7844 16.5416 16.873 17.453C15.9616 18.3644 14.6744 18.8601 13.0115 18.94C12.308 18.98 11.0408 19 9.21 19C7.37919 19 6.11202 18.98 5.40848 18.94C3.74556 18.8601 2.4584 18.3644 1.54699 17.453C0.635586 16.5416 0.139909 15.2544 0.0599609 13.5915C0.019987 12.888 0 11.6208 0 9.79C0 7.95919 0.019987 6.69202 0.0599609 5.98848C0.139909 4.32556 0.635586 3.0384 1.54699 2.12699C2.4584 1.21559 3.74556 0.719909 5.40848 0.639961C6.11202 0.599987 7.37919 0.58 9.21 0.58C11.0408 0.58 12.308 0.599987 13.0115 0.639961C14.6744 0.719909 15.9616 1.21559 16.873 2.12699C17.7844 3.0384 18.2801 4.32556 18.36 5.98848Z" fill="#D59958"/>
                                                </svg>
                                            </a>
                                        </li>
                                    @endif
                                    @if(array_key_exists('telegram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['telegram']))
                                        <li>
                                            <a href="{{ $applicationGlobalOptions['telegram'] }}" target="_blank">
                                                <svg width="18" height="16" viewBox="0 0 18 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M15.2632 14.951V14.9492L15.2795 14.9097L18 1.03268V0.988627C18 0.64262 17.873 0.340666 17.599 0.159861C17.3587 0.00108267 17.082 -0.00993075 16.8879 0.00475392C16.7072 0.0212889 16.5289 0.0582304 16.3563 0.114889C16.2827 0.138894 16.2101 0.165837 16.1386 0.195655L16.1268 0.200243L0.959514 6.22004L0.954979 6.22188C0.908866 6.23781 0.863732 6.2565 0.819816 6.27786C0.711948 6.3269 0.608454 6.38524 0.510484 6.45224C0.31545 6.58808 -0.055567 6.90747 0.0070251 7.41409C0.0587317 7.83444 0.344478 8.1006 0.537698 8.23919C0.651747 8.32005 0.774365 8.38777 0.903272 8.4411L0.9323 8.45395L0.941372 8.4567L0.947722 8.45946L3.60199 9.36348C3.59292 9.53144 3.60925 9.70306 3.6537 9.87286L4.98265 14.9749C5.05525 15.253 5.21212 15.5011 5.43124 15.6844C5.65037 15.8676 5.92072 15.9769 6.20435 15.9967C6.48798 16.0166 6.77063 15.946 7.0126 15.795C7.25457 15.644 7.44371 15.4201 7.55346 15.1547L9.62898 12.9098L13.1931 15.6742L13.2439 15.6962C13.5677 15.8394 13.8698 15.8844 14.1465 15.8468C14.4232 15.8082 14.6427 15.6907 14.8078 15.5577C14.9988 15.401 15.151 15.2015 15.2523 14.9749L15.2596 14.9593L15.2623 14.9538L15.2632 14.951ZM4.96904 9.52226C4.95432 9.46565 4.95782 9.40577 4.97903 9.3513C5.00023 9.29684 5.03804 9.25063 5.08697 9.21939L14.0866 3.4373C14.0866 3.4373 14.6164 3.11148 14.5973 3.4373C14.5973 3.4373 14.6917 3.4942 14.4078 3.76128C14.1392 4.01551 7.99342 10.0188 7.37113 10.6264C7.33653 10.6604 7.31228 10.7037 7.30128 10.7512L6.29799 14.6243L4.96904 9.52226Z" fill="#D59958"/>
                                                </svg>
                                            </a>
                                        </li>
                                    @endif
                                    @if(array_key_exists('viber', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['viber']))
                                        <li class="viber-item">
                                            <a href="{{ $applicationGlobalOptions['viber'] }}" target="_blank">
                                                <svg width="17" height="18" viewBox="0 0 17 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12.8201 0.463759C9.97485 -0.154586 7.0243 -0.154586 4.17908 0.463759L3.87251 0.529701C3.07218 0.703667 2.33713 1.08993 1.74899 1.64559C1.16086 2.20125 0.742685 2.90454 0.540912 3.67735C-0.180304 6.44036 -0.180304 9.33474 0.540912 12.0977C0.733334 12.8348 1.12277 13.5092 1.66974 14.0527C2.21671 14.5963 2.90177 14.9895 3.65547 15.1926L4.07599 17.6334C4.08941 17.7109 4.12396 17.7835 4.17604 17.8436C4.22811 17.9037 4.29582 17.9491 4.37209 17.9752C4.44837 18.0012 4.53042 18.0069 4.60972 17.9916C4.68902 17.9764 4.76266 17.9407 4.82297 17.8884L7.29273 15.7404C9.14672 15.8504 11.0074 15.7063 12.8201 15.3122L13.1275 15.2463C13.9279 15.0723 14.6629 14.686 15.2511 14.1304C15.8392 13.5747 16.2574 12.8714 16.4591 12.0986C17.1803 9.33561 17.1803 6.44124 16.4591 3.67823C16.2573 2.90531 15.839 2.20194 15.2507 1.64627C14.6624 1.0906 13.9271 0.704401 13.1266 0.53058L12.8201 0.463759ZM4.94235 3.60613C4.77427 3.58235 4.60289 3.61524 4.45671 3.69933H4.44405C4.10493 3.89276 3.79926 4.13631 3.53881 4.42294C3.32176 4.66648 3.2042 4.91267 3.17345 5.15006C3.15537 5.29074 3.16803 5.43317 3.21053 5.5677L3.22681 5.57649C3.47098 6.27372 3.78931 6.94457 4.17818 7.57586C4.67959 8.46256 5.29663 9.28262 6.01399 10.0157L6.0357 10.0456L6.07006 10.0702L6.09086 10.094L6.11618 10.1151C6.87287 10.8146 7.71833 11.4174 8.63206 11.9087C9.67658 12.4617 10.3105 12.7229 10.6912 12.8319V12.8372C10.8025 12.8706 10.9038 12.8855 11.006 12.8855C11.3302 12.8622 11.6371 12.7341 11.8777 12.5215C12.1717 12.2683 12.4204 11.9703 12.6139 11.6388V11.6326C12.7957 11.2985 12.7342 10.9838 12.4719 10.7701C11.9451 10.3226 11.3755 9.92514 10.7708 9.58314C10.3657 9.36949 9.95421 9.49874 9.78781 9.71503L9.43241 10.1511C9.24973 10.3674 8.91874 10.3375 8.91874 10.3375L8.9097 10.3428C6.43994 9.72998 5.78067 7.2989 5.78067 7.2989C5.78067 7.2989 5.74992 6.96831 5.97872 6.7995L6.42366 6.45132C6.63708 6.28251 6.7854 5.88334 6.5566 5.48944C6.20716 4.90103 5.79916 4.34739 5.33845 3.83649C5.23796 3.71625 5.09705 3.63436 4.94054 3.60525L4.94235 3.60613ZM9.11498 2.5493C8.99506 2.5493 8.88005 2.59561 8.79525 2.67806C8.71045 2.7605 8.66281 2.87232 8.66281 2.98891C8.66281 3.1055 8.71045 3.21732 8.79525 3.29977C8.88005 3.38221 8.99506 3.42853 9.11498 3.42853C10.259 3.42853 11.2085 3.79165 11.96 4.488C12.3462 4.86871 12.6473 5.31975 12.8445 5.81388C13.0425 6.30889 13.133 6.83731 13.1095 7.3666C13.107 7.42433 13.1162 7.48198 13.1366 7.53624C13.157 7.59051 13.1882 7.64033 13.2285 7.68287C13.3097 7.76877 13.4227 7.81979 13.5426 7.82468C13.6626 7.82958 13.7796 7.78796 13.8679 7.70898C13.9563 7.63 14.0088 7.52012 14.0138 7.40353C14.0419 6.75245 13.9307 6.10278 13.6873 5.4956C13.4429 4.88553 13.0723 4.33072 12.5985 3.8655L12.5895 3.85671C11.6571 2.99067 10.4769 2.5493 9.11498 2.5493ZM9.08423 3.99475C8.96431 3.99475 8.8493 4.04107 8.7645 4.12351C8.6797 4.20596 8.63206 4.31777 8.63206 4.43437C8.63206 4.55096 8.6797 4.66278 8.7645 4.74522C8.8493 4.82767 8.96431 4.87398 9.08423 4.87398H9.09961C9.92437 4.93113 10.5249 5.19842 10.9454 5.63716C11.3767 6.08908 11.6001 6.65091 11.5829 7.3455C11.5802 7.46209 11.6252 7.57498 11.708 7.65932C11.7909 7.74366 11.9048 7.79255 12.0247 7.79523C12.1446 7.79791 12.2607 7.75416 12.3475 7.67362C12.4342 7.59307 12.4845 7.48232 12.4873 7.36572C12.509 6.45396 12.2069 5.66705 11.6083 5.03928V5.03752C10.996 4.3992 10.1559 4.06157 9.14483 3.99563L9.12945 3.99387L9.08423 3.99475ZM9.06705 5.46746C9.00654 5.46227 8.94557 5.46899 8.88779 5.48721C8.83 5.50544 8.77659 5.53479 8.73073 5.57353C8.68488 5.61227 8.64752 5.65959 8.62089 5.71268C8.59426 5.76576 8.57891 5.82352 8.57575 5.8825C8.57259 5.94148 8.58168 6.00048 8.60249 6.05597C8.6233 6.11146 8.6554 6.1623 8.69687 6.20546C8.73833 6.24862 8.78832 6.28322 8.84384 6.30718C8.89936 6.33114 8.95929 6.34399 9.02003 6.34494C9.39804 6.36428 9.6395 6.47506 9.79143 6.62365C9.94426 6.77312 10.0582 7.01315 10.079 7.38858C10.0801 7.44758 10.0935 7.50575 10.1182 7.55963C10.143 7.61351 10.1786 7.662 10.223 7.7022C10.2675 7.7424 10.3197 7.7735 10.3768 7.79363C10.4338 7.81377 10.4945 7.82253 10.5551 7.81941C10.6157 7.81628 10.675 7.80132 10.7296 7.77542C10.7841 7.74952 10.8327 7.71322 10.8725 7.66867C10.9123 7.62411 10.9425 7.57223 10.9612 7.51611C10.9799 7.45998 10.9868 7.40076 10.9815 7.34198C10.9526 6.81445 10.7826 6.34845 10.4344 6.00555C10.0844 5.66265 9.60785 5.4956 9.06705 5.46746Z" fill="#D59958"/>
                                                </svg>
                                            </a>
                                        </li>
                                    @endif
                                    @if(array_key_exists('facebook', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['facebook']))
                                        <li>
                                            <a href="{{ $applicationGlobalOptions['facebook'] }}" target="_blank">
                                                <svg width="9" height="18" viewBox="0 0 9 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M9 0.129807V2.98558H7.36458C6.76736 2.98558 6.36458 3.11538 6.15625 3.375C5.94792 3.63462 5.84375 4.02404 5.84375 4.54327V6.58774H8.89583L8.48958 9.78966H5.84375V18H2.65625V9.78966H0V6.58774H2.65625V4.22957C2.65625 2.88822 3.01736 1.84796 3.73958 1.10877C4.46181 0.369591 5.42361 -4.76837e-07 6.625 -4.76837e-07C7.64583 -4.76837e-07 8.4375 0.0432688 9 0.129807Z" fill="#D59958"/>
                                                </svg>
                                            </a>
                                        </li>
                                    @endif
                                </ul>
                                <div class="art-card-icons">
                                    <img src="{{ asset('storage/bg-images/dss.png') }}" class="card-dss" alt="">
                                    <img src="{{ asset('storage/bg-images/cards.png') }}" class="visa-mastercard" alt="">
                                </div>
                            </div>
                        </div>

                        <div class="footer-content-right">

                            <div class="col-one">
                                <span class="art-footer-title h5 d-block">{{ trans('base.navigation') }}</span>
                                <ul itemscope itemtype="https://schema.org/SiteNavigationElement">
                                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{trans('base.about_us')}}</a></li>
                                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{trans('base.delivery')}}</a></li>
                                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{trans('base.blog')}}</a></li>
                                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{trans('base.services')}}</a></li>
                                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{trans('base.contacts')}}</a></li>
                                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.faq.page') }}">{{ trans('base.faq') }}</a></li>
                                    {{--                                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">{{trans('base.our_works')}}</a></li>--}}
                                </ul>
                            </div>

                            <div class="col-two">
                                <span class="art-footer-title h5 d-block">{{ trans('base.footer_cat') }}</span>
                                <ul itemscope itemtype="https://schema.org/SiteNavigationElement">
                                    @foreach($productTypes as $productType)
                                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">{{ $productType->name }}</a></li>
                                    @endforeach

                                        <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"><a itemprop="item" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                                        'categorySlug' => 'dverni-rucky',
                                        'productTypeSlug' => 'aksessuar'
                                        ]) }}">{{ trans('shop.door_handles') }}</a>
                                        </li>
                                </ul>
                            </div>

                            @if( !is_null($contactsFooter) )
                                <div class="col-three">
                                    <span class="art-footer-title h5 d-block">{{ trans('base.footer_address') }}</span>
                                    <div class="art-address-wrapper">
                                        <span class="city">{{ $contactsFooter['city_one'] .', '.$contactsFooter['address_one'] }}</span>
                                        <span class="phone">{{ $contactsFooter['phone_one'] }}</span>
                                        <span class="email">{{ $contactsFooter['email_one'] }}</span>
                                        <span class="hours">{{ trans('base.working_hours') }}</span>
                                    </div>
                                    <div class="art-address-wrapper">
                                        <span class="city">{{ $contactsFooter['city_two'] .', '.$contactsFooter['address_two'] }}</span>
                                        <span class="phone">{{ $contactsFooter['phone_two'] }}</span>
                                        <span class="email">{{ $contactsFooter['email_two'] }}</span>
                                        <span class="hours">{{ trans('base.working_hours') }}</span>
                                    </div>
                                    @if($contactsFooter['address_three'])
                                        <div class="art-address-wrapper">
                                            <span class="city">{{ $contactsFooter['city_three'] .', '.$contactsFooter['address_three'] }}</span>
                                            <span class="phone">{{ $contactsFooter['phone_three'] }}</span>
                                            <span class="email">{{ $contactsFooter['email_three'] }}</span>
                                            <span class="hours">{{ trans('base.working_hours') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                        </div>
                    </div>
                </div>

            </div>

            <div class="footer-bottom">
                <div class="container">
                    <div class="row">
                        <div class="col-12 art-footer-bottom-wrapper">
                            <p class="art-copyright">BONA © {{ date('Y') .' '. trans('base.all_rights_reserved') }}</p>
                            <p>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'exchange-and-return']) }}" class="mr-4">{{ trans('base.exchange_and_return') }}</a>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'polityka-konfidencinosti']) }}" class="mr-4">{{ trans('base.policy') }}</a>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">{{ trans('base.agreement') }}</a>
                            </p>
                        </div>
                    </div>

                </div>
            </div>

        </footer>
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
