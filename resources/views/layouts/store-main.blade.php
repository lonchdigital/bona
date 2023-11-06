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
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&amp;subset=latin-ext" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500" rel="stylesheet">

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




    <nav class="main-website-header navbar-fixed111">

        <div class="container">

            <div class="navigation navigation-top">

                <div class="navigation-left-side">
                    <ul>
                        <li><a href="#">Про нас</a></li>
                        <li><a href="#">Доставка</a></li>
                        <li><a href="#">Оплата</a></li>
                        <li><a href="#">Блог</a></li>
                        <li><a href="#">Послуги</a></li>
                        <li><a href="#">Контакти</a></li>
                    </ul>
                </div>

                <div class="navigation-right-side">
                    <ul class="header-main-others">
                        <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fa fa-youtube"></i></a></li>
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
                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}" class="logo"><img src="{{ asset('storage/logo/logo.svg') }}" alt="Logo"></a>

                    <div class="header-search">
                        <a href="javascript:void(0);" class="website-search-link"><i class="icon icon-magnifier"></i></a>
                        <input type="text" placeholder="Пошук...">
                    </div>
                </div>

                <div class="navigation-bottom-right">
                    <a href="#" class="art-header-phone">+380 (67) 953 44 44</a>
                    <a href="" class="btn btn-main art-header-coll-button">Виклик замірника</a>

                    <ul class="header-main-others">

                        <li><a href="#" class="open-login"><i class="icon icon-user"></i></a></li>

                        <!-- BASKET START -->
                        <x-cart-window/>
                        <!-- BASKET END -->
                    </ul>

                </div>


            </div>

            <!-- ==========  Main navigation ========== -->

            <div class="navigation navigation-main">



                <div class="floating-menu">

                    <!-- Mobile toggle menu trigger-->

                    <div class="close-menu-wrapper">
                        <span class="close-menu"><i class="icon icon-cross"></i></span>
                    </div>

                    <ul>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">{{ trans('base.menu_home') }}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'mizkimnatni-dveri']) }}">{{ trans('base.mizkimnatni_dveri') }}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'pryhovani-dveri']) }}">{{ trans('base.pryhovani_dveri') }}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'vhidni-dveri']) }}">{{ trans('base.vhidni_dveri') }}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'tehnicni-dveri']) }}">{{ trans('base.tehnicni_dveri') }}</a></li>
                        <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => 'rozsuvni-dveri']) }}">{{ trans('base.rozsuvni_dveri') }}</a></li>
                        <li><a href="">Скляні перегородки</a></li>
                        <li><a href="">Фурнітура</a></li>

                    </ul>
                </div> <!--/floating-menu-->
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
                                    <li><a href="#"><i class="fa fa-facebook"></i></a></li>
                                    <li><a href="#"><i class="fa fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa fa-google-plus"></i></a></li>
                                    <li><a href="#"><i class="fa fa-youtube"></i></a></li>
                                    <li><a href="#"><i class="fa fa-instagram"></i></a></li>
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
