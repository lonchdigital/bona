<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Mobile Web-app fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Meta tags -->
    <meta name="description" content="">
    <meta name="author" content="">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:image" content="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
    <meta property="og:site_name" content="{{ mb_strtoupper(config('app.url')) }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ Vite::asset('resources/img/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
{{--    <link rel="icon" type="image/png" sizes="16x16" href="{{ Vite::asset('resources/img/favicon-16x16.png') }}">--}}
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    <meta name="robots" content="noindex">
    @hasSection('title')
        @yield('title')
    @else
        <title>{{ config('app.name') . ' - ' . trans('base.site_title') }}</title>
    @endif
    @stack('head')
    @vite(['resources/scss/libs.scss'])

{{--    @vite(['resources/css/styles.js'])--}}
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/furniture-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/linear-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/ion-range-slider.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/theme-additional.css') }}">



    <!--Google fonts-->
{{--    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&amp;subset=latin-ext" rel="stylesheet">--}}

    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,600;1,700&family=Tenor+Sans&display=swap" rel="stylesheet">


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<div class="page-loader"></div>

<div class="wrapper">

    <!--Use class "navbar-fixed" or "navbar-default" -->
    <!--If you use "navbar-fixed" it will be sticky menu on scroll (only for large screens)-->

    <!-- ======================== Navigation ======================== -->




    <nav class="main-website-header">

        <div class="container">

            <div class="navigation navigation-top">

                <div class="navigation-left-side">
                    <ul>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.about-us') }}">{{trans('base.about_us')}}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{trans('base.delivery')}}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{trans('base.blog')}}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.services') }}">{{trans('base.services')}}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.contacts') }}">{{trans('base.contacts')}}</a></li>
                    </ul>
                </div>


                <div class="navigation-right-side">
                    <ul class="header-main-others">
                        @if(array_key_exists('instagram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['instagram']))
                            <li><a href="{{ $applicationGlobalOptions['instagram'] }}"><i class="fa fa-instagram"></i></a></li>
                        @endif
                        @if(array_key_exists('telegram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['telegram']))
                            <li><a href="{{ $applicationGlobalOptions['telegram'] }}"><i class="fa fa-telegram"></i></a></li>
                        @endif
                        @if(array_key_exists('viber', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['viber']))
                            <li><a href="{{ $applicationGlobalOptions['viber'] }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M444 49.9C431.3 38.2 379.9.9 265.3.4c0 0-135.1-8.1-200.9 52.3C27.8 89.3 14.9 143 13.5 209.5c-1.4 66.5-3.1 191.1 117 224.9h.1l-.1 51.6s-.8 20.9 13 25.1c16.6 5.2 26.4-10.7 42.3-27.8 8.7-9.4 20.7-23.2 29.8-33.7 82.2 6.9 145.3-8.9 152.5-11.2 16.6-5.4 110.5-17.4 125.7-142 15.8-128.6-7.6-209.8-49.8-246.5zM457.9 287c-12.9 104-89 110.6-103 115.1-6 1.9-61.5 15.7-131.2 11.2 0 0-52 62.7-68.2 79-5.3 5.3-11.1 4.8-11-5.7 0-6.9.4-85.7.4-85.7-.1 0-.1 0 0 0-101.8-28.2-95.8-134.3-94.7-189.8 1.1-55.5 11.6-101 42.6-131.6 55.7-50.5 170.4-43 170.4-43 96.9.4 143.3 29.6 154.1 39.4 35.7 30.6 53.9 103.8 40.6 211.1zm-139-80.8c.4 8.6-12.5 9.2-12.9.6-1.1-22-11.4-32.7-32.6-33.9-8.6-.5-7.8-13.4.7-12.9 27.9 1.5 43.4 17.5 44.8 46.2zm20.3 11.3c1-42.4-25.5-75.6-75.8-79.3-8.5-.6-7.6-13.5.9-12.9 58 4.2 88.9 44.1 87.8 92.5-.1 8.6-13.1 8.2-12.9-.3zm47 13.4c.1 8.6-12.9 8.7-12.9.1-.6-81.5-54.9-125.9-120.8-126.4-8.5-.1-8.5-12.9 0-12.9 73.7.5 133 51.4 133.7 139.2zM374.9 329v.2c-10.8 19-31 40-51.8 33.3l-.2-.3c-21.1-5.9-70.8-31.5-102.2-56.5-16.2-12.8-31-27.9-42.4-42.4-10.3-12.9-20.7-28.2-30.8-46.6-21.3-38.5-26-55.7-26-55.7-6.7-20.8 14.2-41 33.3-51.8h.2c9.2-4.8 18-3.2 23.9 3.9 0 0 12.4 14.8 17.7 22.1 5 6.8 11.7 17.7 15.2 23.8 6.1 10.9 2.3 22-3.7 26.6l-12 9.6c-6.1 4.9-5.3 14-5.3 14s17.8 67.3 84.3 84.3c0 0 9.1.8 14-5.3l9.6-12c4.6-6 15.7-9.8 26.6-3.7 14.7 8.3 33.4 21.2 45.8 32.9 7 5.7 8.6 14.4 3.8 23.6z"/></svg></a></li>
                        @endif
                    </ul>

                    <ul class="header-main-others">
                        <!--Language selector-->
                        <li class="nav-settings">
                            <a href="javascript:void(0);" class="nav-settings-value"> {{ mb_strtoupper(app()->getLocale()) }}</a>
                            <ul class="nav-settings-list">
                                @foreach(app()->make(\App\Services\Application\ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage)
                                    @if (mb_strtoupper($availableLanguage) !== mb_strtoupper(app()->getLocale()))
                                        <li>
                                            <a href="{{ route('locale.change', ['newLocale' => mb_strtolower($availableLanguage)]) }}">
                                                {{ mb_strtoupper($availableLanguage) }}
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    </ul>
                </div>

            </div> <!--/navigation-top-->


            <div class="navigation-bottom header-main-desktop">

                <div class="navigation-bottom-left">
                    @if(array_key_exists('logoLight', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['logoLight']))
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}" class="logo"><img src="{{ '/storage/' . $applicationGlobalOptions['logoLight'] }}" alt="Logo"></a>
                    @endif

                    <div class="header-search">
                        <a href="javascript:void(0);" class="website-search-link"><i class="icon icon-magnifier"></i></a>
                        <form action="" class="main-header-search-form">
                            @csrf
                            <input id="main-header-search" type="text" placeholder="{{ trans('base.select2_search') }}">
                            <div id="main-header-search-result"></div>
                        </form>
                    </div>

                </div>

                <div class="navigation-bottom-right">

                    <i class="icon icon-magnifier art-search-mobile-icon"></i>

                    @if(array_key_exists('phoneOne', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['phoneOne']))
                        <div class="art-header-phone">
                            <a href="tel:{{ $applicationGlobalOptions['phoneOne'] }}" class="phone-full-number">{{ $applicationGlobalOptions['phoneOne'] }}</a>
                            <a href="tel:{{ $applicationGlobalOptions['phoneOne'] }}" class="phone-icon">
                                <svg width="20" height="20" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M17.3234 12.5746L12.8964 10.5937C12.7248 10.5194 12.5374 10.4891 12.3511 10.5054C12.1648 10.5218 11.9856 10.5843 11.8295 10.6874C11.8171 10.6953 11.8052 10.7041 11.7939 10.7137L9.46889 12.6956C9.41715 12.7271 9.35842 12.7454 9.29792 12.7489C9.23741 12.7523 9.17699 12.7407 9.12201 12.7152C7.59949 11.9802 6.02353 10.4165 5.28852 8.91176C5.26252 8.8572 5.25045 8.79704 5.2534 8.73667C5.25634 8.6763 5.27421 8.6176 5.3054 8.56582L7.29292 6.20236C7.3023 6.19111 7.31074 6.17892 7.31917 6.16673C7.42192 6.01092 7.48428 5.83199 7.50065 5.64607C7.51701 5.46016 7.48687 5.27309 7.41293 5.10172L5.42821 0.682294C5.3324 0.45846 5.16655 0.271752 4.95558 0.150206C4.7446 0.0286611 4.49989 -0.0211601 4.25819 0.00822439C3.0784 0.163776 1.99557 0.743479 1.21196 1.63906C0.428349 2.53464 -0.00245624 3.68484 1.0535e-05 4.87484C1.0535e-05 12.1124 5.88759 18 13.1252 18C14.3152 18.0025 15.4654 17.5717 16.361 16.788C17.2566 16.0044 17.8363 14.9216 17.9918 13.7418C18.021 13.5012 17.9717 13.2576 17.8512 13.0473C17.7308 12.837 17.5457 12.6712 17.3234 12.5746ZM17.2502 13.6481C17.1179 14.6472 16.6261 15.5639 15.8669 16.2269C15.1077 16.8898 14.1331 17.2535 13.1252 17.25C6.30197 17.25 0.750021 11.698 0.750021 4.87484C0.746529 3.86697 1.11024 2.89232 1.77315 2.13313C2.43607 1.37394 3.3528 0.882183 4.35194 0.749795C4.36693 0.748864 4.38196 0.748864 4.39695 0.749795C4.47093 0.750426 4.54307 0.772926 4.60429 0.814465C4.66552 0.856003 4.71309 0.914724 4.74101 0.983236L6.7201 5.40266C6.74319 5.45667 6.75327 5.51536 6.74953 5.57398C6.74578 5.6326 6.72832 5.68953 6.69854 5.74016L4.71195 8.10269C4.70258 8.11488 4.6932 8.12613 4.68476 8.13925C4.57893 8.3011 4.51659 8.4875 4.50378 8.68045C4.49098 8.87341 4.52812 9.06641 4.61164 9.24083C5.42634 10.9087 7.10636 12.5765 8.79294 13.3912C8.96855 13.4742 9.16265 13.5103 9.35635 13.496C9.55005 13.4817 9.73674 13.4175 9.89827 13.3096L9.93296 13.2834L12.2608 11.3034C12.3106 11.2729 12.3669 11.2548 12.4251 11.2504C12.4833 11.246 12.5417 11.2555 12.5955 11.278L17.0215 13.2618C17.0962 13.2929 17.1588 13.3473 17.2001 13.4169C17.2413 13.4865 17.2589 13.5676 17.2502 13.6481Z" fill="white"/>
                                </svg>
                            </a>
                        </div>
                    @endif

                    <a href="" class="btn btn-main art-header-coll-button">{{ trans('base.call_measurer') }}</a>

                    <ul class="header-main-others">
                        <!-- BASKET START -->
                        <x-cart-window/>
                        <!-- BASKET END -->
                    </ul>

                    <div class="art-hamburger-button">
                        <button class="hamburger hamburger--collapse-r" type="button">
                            <span class="hamburger-box">
                              <span class="hamburger-inner"></span>
                            </span>
                        </button>
                    </div>

                </div>


                <div id="art-hamburger-menu">
                    <div class="container">
                        <div class="art-hamburger-header font-title">
                            <span data-id="art-cat" class="active">Категорії</span>
                            <span data-id="art-nav">Навігація</span>
                        </div>
                        <div class="art-hamburger-data">
                            <div class="art-list-items d-block" data-id="art-cat">
                                <ul>
                                    <li><a href="">Білі двері</a></li>
                                    <li><a href="">ФАРБОВАні двері</a></li>
                                    <li><a href="">вуличні двері</a></li>
                                    <li><a href="">Акційні пропозиції</a></li>
                                </ul>
                            </div>
                            <div class="art-list-items d-none" data-id="art-nav">
                                <ul>
                                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'mizkimnatni-dveri']) }}">{{ trans('base.mizkimnatni_dveri') }}</a></li>
                                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'pryhovani-dveri']) }}">{{ trans('base.pryhovani_dveri') }}</a></li>
                                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'vhidni-dveri']) }}">{{ trans('base.vhidni_dveri') }}</a></li>
                                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'laminat']) }}">{{ trans('base.laminat') }}</a></li>
                                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'rozsuvni-dveri']) }}">{{ trans('base.rozsuvni_dveri') }}</a></li>
                                    <li><a href="">Скляні перегородки</a></li>
                                    <li><a href="">Фурнітура</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="art-hamburger-bottom">
                            <div class="hamburger-bottom-left">
                                <ul>
                                    @if(array_key_exists('instagram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['instagram']))
                                        <li><a href="{{ $applicationGlobalOptions['instagram'] }}"><i class="fa fa-instagram"></i></a></li>
                                    @endif
                                    @if(array_key_exists('telegram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['telegram']))
                                        <li><a href="{{ $applicationGlobalOptions['telegram'] }}"><i class="fa fa-telegram"></i></a></li>
                                    @endif
                                    @if(array_key_exists('viber', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['viber']))
                                        <li><a href="{{ $applicationGlobalOptions['viber'] }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M444 49.9C431.3 38.2 379.9.9 265.3.4c0 0-135.1-8.1-200.9 52.3C27.8 89.3 14.9 143 13.5 209.5c-1.4 66.5-3.1 191.1 117 224.9h.1l-.1 51.6s-.8 20.9 13 25.1c16.6 5.2 26.4-10.7 42.3-27.8 8.7-9.4 20.7-23.2 29.8-33.7 82.2 6.9 145.3-8.9 152.5-11.2 16.6-5.4 110.5-17.4 125.7-142 15.8-128.6-7.6-209.8-49.8-246.5zM457.9 287c-12.9 104-89 110.6-103 115.1-6 1.9-61.5 15.7-131.2 11.2 0 0-52 62.7-68.2 79-5.3 5.3-11.1 4.8-11-5.7 0-6.9.4-85.7.4-85.7-.1 0-.1 0 0 0-101.8-28.2-95.8-134.3-94.7-189.8 1.1-55.5 11.6-101 42.6-131.6 55.7-50.5 170.4-43 170.4-43 96.9.4 143.3 29.6 154.1 39.4 35.7 30.6 53.9 103.8 40.6 211.1zm-139-80.8c.4 8.6-12.5 9.2-12.9.6-1.1-22-11.4-32.7-32.6-33.9-8.6-.5-7.8-13.4.7-12.9 27.9 1.5 43.4 17.5 44.8 46.2zm20.3 11.3c1-42.4-25.5-75.6-75.8-79.3-8.5-.6-7.6-13.5.9-12.9 58 4.2 88.9 44.1 87.8 92.5-.1 8.6-13.1 8.2-12.9-.3zm47 13.4c.1 8.6-12.9 8.7-12.9.1-.6-81.5-54.9-125.9-120.8-126.4-8.5-.1-8.5-12.9 0-12.9 73.7.5 133 51.4 133.7 139.2zM374.9 329v.2c-10.8 19-31 40-51.8 33.3l-.2-.3c-21.1-5.9-70.8-31.5-102.2-56.5-16.2-12.8-31-27.9-42.4-42.4-10.3-12.9-20.7-28.2-30.8-46.6-21.3-38.5-26-55.7-26-55.7-6.7-20.8 14.2-41 33.3-51.8h.2c9.2-4.8 18-3.2 23.9 3.9 0 0 12.4 14.8 17.7 22.1 5 6.8 11.7 17.7 15.2 23.8 6.1 10.9 2.3 22-3.7 26.6l-12 9.6c-6.1 4.9-5.3 14-5.3 14s17.8 67.3 84.3 84.3c0 0 9.1.8 14-5.3l9.6-12c4.6-6 15.7-9.8 26.6-3.7 14.7 8.3 33.4 21.2 45.8 32.9 7 5.7 8.6 14.4 3.8 23.6z"/></svg></a></li>
                                    @endif
                                </ul>
                            </div>
                            <div class="hamburger-bottom-right">
                                <ul>
                                    @foreach(app()->make(\App\Services\Application\ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage)
                                        @if (mb_strtoupper($availableLanguage) !== mb_strtoupper(app()->getLocale()))
                                            <li>
                                                <a href="{{ route('locale.change', ['newLocale' => mb_strtolower($availableLanguage)]) }}">
                                                    {{ mb_strtoupper($availableLanguage) }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <!-- ==========  Main navigation ========== -->

            <div class="navigation navigation-main">
                <ul class="main-menu-container">
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'mizkimnatni-dveri']) }}">{{ trans('base.mizkimnatni_dveri') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'pryhovani-dveri']) }}">{{ trans('base.pryhovani_dveri') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'vhidni-dveri']) }}">{{ trans('base.vhidni_dveri') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'laminat']) }}">{{ trans('base.laminat') }}</a></li>
                    <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'rozsuvni-dveri']) }}">{{ trans('base.rozsuvni_dveri') }}</a></li>
                    <li><a href="">Скляні перегородки</a></li>
                    <li><a href="">Фурнітура</a></li>
                </ul>
            </div> <!--/navigation-main-->

        </div> <!--/container-->
    </nav>



    @yield('content')


    @hasSection('noFooter')
    @else
        <!-- ================== Footer  ================== -->
            <footer class="art-site-footer">


                <div class="footer-content">

                    <div class="container">
                        <div class="art-flex-row">
                            <div class="footer-content-left">
                                <img src="{{ asset('storage/logo/logo.svg') }}" alt="Logo">
                                <p class="art-footer-description">Bust master shore what the sainted store tell stood sitting word thy unbrokenquit tossed more beguiling to rare stood take. Sent that maiden entrance door the and i to if me entrance the startled yore the sainted velvet raven still bird cushioned more then quoth and just a lenore back</p>
                                <ul class="art-footer-social">

                                    @if(array_key_exists('instagram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['instagram']))
                                        <li><a href="{{ $applicationGlobalOptions['instagram'] }}"><i class="fa fa-instagram"></i></a></li>
                                    @endif
                                    @if(array_key_exists('telegram', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['telegram']))
                                        <li><a href="{{ $applicationGlobalOptions['telegram'] }}"><i class="fa fa-telegram"></i></a></li>
                                    @endif
                                    @if(array_key_exists('viber', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['viber']))
                                        <li class="viber-item"><a href="{{ $applicationGlobalOptions['viber'] }}"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M444 49.9C431.3 38.2 379.9.9 265.3.4c0 0-135.1-8.1-200.9 52.3C27.8 89.3 14.9 143 13.5 209.5c-1.4 66.5-3.1 191.1 117 224.9h.1l-.1 51.6s-.8 20.9 13 25.1c16.6 5.2 26.4-10.7 42.3-27.8 8.7-9.4 20.7-23.2 29.8-33.7 82.2 6.9 145.3-8.9 152.5-11.2 16.6-5.4 110.5-17.4 125.7-142 15.8-128.6-7.6-209.8-49.8-246.5zM457.9 287c-12.9 104-89 110.6-103 115.1-6 1.9-61.5 15.7-131.2 11.2 0 0-52 62.7-68.2 79-5.3 5.3-11.1 4.8-11-5.7 0-6.9.4-85.7.4-85.7-.1 0-.1 0 0 0-101.8-28.2-95.8-134.3-94.7-189.8 1.1-55.5 11.6-101 42.6-131.6 55.7-50.5 170.4-43 170.4-43 96.9.4 143.3 29.6 154.1 39.4 35.7 30.6 53.9 103.8 40.6 211.1zm-139-80.8c.4 8.6-12.5 9.2-12.9.6-1.1-22-11.4-32.7-32.6-33.9-8.6-.5-7.8-13.4.7-12.9 27.9 1.5 43.4 17.5 44.8 46.2zm20.3 11.3c1-42.4-25.5-75.6-75.8-79.3-8.5-.6-7.6-13.5.9-12.9 58 4.2 88.9 44.1 87.8 92.5-.1 8.6-13.1 8.2-12.9-.3zm47 13.4c.1 8.6-12.9 8.7-12.9.1-.6-81.5-54.9-125.9-120.8-126.4-8.5-.1-8.5-12.9 0-12.9 73.7.5 133 51.4 133.7 139.2zM374.9 329v.2c-10.8 19-31 40-51.8 33.3l-.2-.3c-21.1-5.9-70.8-31.5-102.2-56.5-16.2-12.8-31-27.9-42.4-42.4-10.3-12.9-20.7-28.2-30.8-46.6-21.3-38.5-26-55.7-26-55.7-6.7-20.8 14.2-41 33.3-51.8h.2c9.2-4.8 18-3.2 23.9 3.9 0 0 12.4 14.8 17.7 22.1 5 6.8 11.7 17.7 15.2 23.8 6.1 10.9 2.3 22-3.7 26.6l-12 9.6c-6.1 4.9-5.3 14-5.3 14s17.8 67.3 84.3 84.3c0 0 9.1.8 14-5.3l9.6-12c4.6-6 15.7-9.8 26.6-3.7 14.7 8.3 33.4 21.2 45.8 32.9 7 5.7 8.6 14.4 3.8 23.6z"/></svg></a></li>
                                    @endif

                                </ul>
                            </div>
                            <div class="footer-content-right">
                                <div class="col-one">
                                    <h5 class="art-footer-title">{{ trans('base.footer_cat') }}</h5>
                                    <ul>
                                        @foreach($productTypes as $productType)
                                            <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $productType->slug]) }}">{{ $productType->name }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="col-two">
                                    <h5 class="art-footer-title">{{ trans('base.footer_address') }}</h5>
                                    <div class="art-address-wrapper">
                                        <span class="city">м. Київ</span>
                                        <span class="phone">+380 (67) 953 44 44</span>
                                        <span class="email">bona-doors@ukr.net</span>
                                    </div>
                                    <div class="art-address-wrapper">
                                        <span class="city">м. Київ</span>
                                        <span class="phone">+380 (67) 953 44 44</span>
                                        <span class="email">bona-doors@ukr.net</span>
                                    </div>
                                    <div class="art-address-wrapper">
                                        <span class="city">м. Київ</span>
                                        <span class="phone">+380 (67) 953 44 44</span>
                                        <span class="email">bona-doors@ukr.net</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


                <div class="footer-bottom">
                    <div class="container">
                        <p>BONA © 2023 Всі права захищені</p>
                        <p><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">{{ trans('base.agreement') }}</a></p>
                    </div>
                </div>

            </footer>
    @endif

</div>

{{-- static scripts start --}}
<script src="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('static-data.script') }}?lang={{ app()->getLocale() }}"></script>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.bootstrap.js') }}"></script>
<script src="{{ asset('assets/js/jquery.magnific-popup.js') }}"></script>
<script src="{{ asset('assets/js/jquery.owl.carousel.js') }}"></script>
<script src="{{ asset('assets/js/jquery.ion.rangeSlider.js') }}"></script>
<script src="{{ asset('assets/js/jquery.isotope.pkgd.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
{{-- static scripts end --}}

{{-- dinamic scripts start --}}
<script>
    const page = '{{ request()->route() ? request()->route()->getName() : '' }}';
    const is_auth = {{ auth()->user() ? 'true' : 'false' }};
    const locale = '{{ app()->getLocale() }}';
    const csrf = '{{ csrf_token() }}';
    const count_of_products_in_cart = {{ $countOfProductInCart }};
    const show_visit_modal = {{ old('modal_type_id') == \App\DataClasses\VisitRequestTypeDataClass::SHOWROOM_VISIT ? 'true' : 'false' }};
    const show_taxi_modal = {{ old('modal_type_id') == \App\DataClasses\VisitRequestTypeDataClass::SHOWROOM_TAXI ? 'true' : 'false' }};
    const show_designer_modal = {{ old('modal_type_id') == \App\DataClasses\VisitRequestTypeDataClass::DESIGNER_APPOINTMENT ? 'true' : 'false' }};
    const show_modal_success = {{ Session::has('modal_success') ? 'true' : 'false' }};
</script>
@stack('dynamic_scripts')
{{-- dinamic scripts end --}}

{{-- static scripts start --}}
<script src="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('static-data.script') }}?lang={{ app()->getLocale() }}"></script>
@vite('resources/js/store/app.js')
{{-- static scripts end --}}

</body>

</html>
