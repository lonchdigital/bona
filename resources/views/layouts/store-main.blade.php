<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=1">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ request()->url() }}">
    <meta property="og:image" content="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
    <meta property="og:site_name" content="{{ mb_strtoupper(config('app.url')) }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ Vite::asset('resources/img/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ Vite::asset('resources/img/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ Vite::asset('resources/img/favicon-16x16.png') }}">
    <meta name="msapplication-TileColor" content="#da532c">
    <meta name="theme-color" content="#ffffff">
    @hasSection('title')
        @yield('title')
    @else
        <title>{{ config('app.name') . ' - ' . trans('base.site_title') }}</title>
    @endif
    @stack('head')
    @vite(['resources/scss/libs.scss'])
    @vite(['resources/scss/main.scss'])
</head>

<body>
<div id="wrapper" class="wrapper">
    <div id="header" class="header">
        <div class="header-top bg-silver-lighten-custom py-md-1 d-none d-lg-block">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="header-top-content top-content justify-content-center align-items-center flex-wrap d-none d-md-flex">
                            <div class="top-content-text text-uppercase">{{ trans('base.showroom_line_1') }}<span>{{ trans('base.showroom_address') }}</span>{{ trans('base.showroom_line_2') }}</div>
                            <div class="top-content-time mr-2 d-flex align-items-center">
                                <svg>
                                    <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-watch"></use>
                                </svg>
                                <span>{{ trans('base.showroom_hours') }}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-black" data-toggle="modal" data-target="#modal-visit">{{ trans('base.showroom_visit') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="header-main pt-2 pb-4">
            <div class="container">
                <div class="header-main-desktop d-none d-md-block">
                    <div class="row position-relative align-items-center align-items-lg-start">
                        <div class="col position-static">
                            <ul class="header-main-socials list-inline mb-7 pt-4">
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank" aria-label="viber">
                                        <svg>
                                            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-viber"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank" aria-label="telegram">
                                        <svg>
                                            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-telegram"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank" aria-label="messenger">
                                        <svg>
                                            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-messenger"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank" aria-label="direct">
                                        <svg>
                                            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-direct"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank" aria-label="whatsapp">
                                        <svg>
                                            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-whatsapp"></use>
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                            <ul class="list-inline header-main-menu d-none d-lg-flex align-items-center mb-0 flex-wrap">
                                <li class="list-inline-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="link-menu">{{ trans('base.wallpapers') }}</a>
                                </li>
                                <li class="list-inline-item list-inline-item-menu brand-menu">
                                    <div class="link-menu link-menu-sub nolink">{{ trans('base.brands') }}</div>
                                    <div class="sub-menu">
                                        <div class="row mb-7">
                                            @php
                                                $countOfRows = 0;
                                            @endphp
                                            @php
                                                $countOfDisplayedElements = 0;
                                            @endphp
                                            <div class="col-12 col-md-4 col-lg">
                                            @foreach($brands as $firstLetter => $brandsByLetter)
                                                    @php
                                                        $countOfDisplayedElements++;
                                                    @endphp
                                                    @if ($countOfDisplayedElements > 13)
                                                        </div>
                                                        <div class="col-12 col-md-4 col-lg">
                                                            @php
                                                                $countOfRows++;
                                                                $countOfDisplayedElements = 0;
                                                            @endphp
                                                    @endif

                                                    @if($countOfRows > 5)
                                                        @break
                                                    @endif
                                                    <ul class="list-unstyled mb-5 sub-menu-list">
                                                        <li class="font-weight-bold">{{ $firstLetter }}</li>
                                                        @foreach($brandsByLetter as $brand)
                                                            @if ($countOfDisplayedElements > 13)
                                                                @break
                                                            @endif

                                                            @php
                                                                $countOfDisplayedElements++;
                                                            @endphp
                                                            <li>
                                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $brand->slug]) }}" class="link-sub-menu">{{ $brand->name }}</a>
                                                            </li>
                                                        @endforeach
                                                    </ul>

                                            @endforeach
                                            </div>
                                        </div>
                                        <div class="d-flex justify-content-center">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brands.list.page') }}" class="btn btn-outline-black">{{ trans('base.all_brands') }}</a>
                                        </div>
                                    </div>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_DELIVERY_AND_PAYMENT)['slug']]) }}" class="link-menu">{{ trans('base.delivery_and_payment') }}</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="link-menu">{{ trans('base.blog') }}</a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_CONTACTS)['slug']]) }}" class="link-menu">{{ trans('base.contacts') }}</a>
                                </li>
                            </ul>
                        </div>
                        <div class="col-auto">
                            <div class="header-main-logo mx-auto">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">
                                    <img src="{{ Vite::asset('resources/img/logo.svg') }}" alt="logo">
                                </a>
                            </div>
                        </div>
                        <div class="col position-static">
                            <div class="header-main-phone d-flex justify-content-end align-items-center pt-4 mb-7">
                                <div class="mr-6 mr-lg-0">
                                    <a href="tel:+08001234567" class="link-phone d-flex align-items-center">
                                        <svg>
                                            <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-phone"></use>
                                        </svg>
                                        0 800 123 45 67
                                    </a>
                                    <div class="header-main-phone-text text-right">
                                        {{ trans('base.all_calls_free') }}
                                    </div>
                                </div>
                                <div class="menu-burger position-relative d-lg-none">
                                    <span class="lines"></span>
                                </div>
                            </div>
                            <div class="header-main-others-wrapper align-items-center justify-content-end d-none d-lg-flex">
                                <ul class="list-inline header-main-others mb-0">
                                    <li class="list-inline-item search">
                                        <div class="i-search">
                                            <svg>
                                                <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-search"></use>
                                            </svg>
                                        </div>
                                        <div class="sub-menu">
                                            <form action="{{ route('store.brand.search.page') }}">
                                                <div class="input-search">
                                                    <input name="search" type="search" placeholder="{{ trans('base.search_by_brand') }}" autocomplete="off" />
                                                </div>
                                            </form>
                                        </div>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.calculator.page') }}" rel="noffolow" aria-label="{{ trans('base.calculator') }}">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-calc"></use>
                                            </svg>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a
                                            @auth
                                                href="{{App\Helpers\MultiLangRoute::getMultiLangRoute('store.wishlist.private.page')}}"
                                            @else
                                                href="{{App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in')}}"
                                            @endauth
                                            aria-label="{{ trans('base.wish_list') }}"
                                            class="i-heart">
                                            <svg>
                                                @auth
                                                    @if ($wishlistEmpty)
                                                        <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                    @else
                                                        <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-filled-hover"></use>
                                                    @endif
                                                @else
                                                    <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                @endauth
                                            </svg>
                                        </a>
                                    </li>

                                    <!-- BASKET START -->
                                    <x-cart-window/>
                                    <!-- BASKET END -->

                                    <!-- USER PROFILE START -->
                                    @auth
                                    <li class="list-inline-item basket-list user-profile-list" id="basket-link">
                                        <x-user-profile-link :user="auth()->user()"/>
                                    </li>
                                    @endauth
                                    @guest
                                    <li class="list-inline-item">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in') }}" aria-label="{{ trans('base.user') }}">
                                            <svg>
                                                <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-person"></use>
                                            </svg>
                                        </a>
                                    </li>
                                    @endguest
                                    <!-- USER PROFILE END -->

                                    <li class="list-inline-item header-main-languages">
                                        <div class="current-lang">
                                            <span>{{ mb_strtoupper(app()->getLocale()) }}</span>
                                            <ul class="sub-menu list-unstyled mb-0 position-absolute py-1">
                                                @foreach(app()->make(\App\Services\Application\ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage)
                                                    @if (mb_strtoupper($availableLanguage) !== mb_strtoupper(app()->getLocale()))
                                                        <li>
                                                            <a href="{{ route('locale.change', ['newLocale' => mb_strtolower($availableLanguage)]) }}" class="px-6">
                                                                {{ mb_strtoupper($availableLanguage) }}
                                                            </a>
                                                        </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="header-main-mobile d-md-none">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-6 d-flex align-items-center">
                            <div class="menu-burger position-relative d-lg-none mr-5">
                                <span class="lines"></span>
                            </div>
                            <div class="header-main-logo d-flex align-items-center">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">
                                    <img src="{{ Vite::asset('resources/img/logo-m.svg') }}" alt="logo">
                                </a>
                            </div>
                        </div>
                        <div class="col-6 d-flex align-items-center justify-content-end">
                            <div class="header-main-others-wrapper align-items-center justify-content-end d-flex">
                                <ul class="list-inline header-main-others mb-0">
                                    <li class="list-inline-item mr-5">
                                        <a href="#" class="i-heart">
                                            <svg>
                                                <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                            </svg>
                                        </a>
                                    </li>
                                    <x-cart-window/>
                                    <li class="list-inline-item header-main-languages">
                                        <div class="current-lang">
                                            <span>{{ mb_strtoupper(app()->getLocale()) }}</span>
                                            <ul class="sub-menu list-unstyled mb-0 position-absolute py-1">
                                                @foreach(app()->make(\App\Services\Application\ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage)
                                                    @if (mb_strtoupper($availableLanguage) !== mb_strtoupper(app()->getLocale()))
                                                    <li>
                                                        <a href="{{ route('locale.change', ['newLocale' => mb_strtolower($availableLanguage)]) }}" class="px-6">
                                                            {{ mb_strtoupper($availableLanguage) }}
                                                        </a>
                                                    </li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="menu-mobile d-lg-none bg-white px-4 pt-4 pb-2 p-md-4">
                    <ul class="list-inline header-main-menu d-lg-none mb-0">
                        <li class="list-inline-item search">
                            <div class="sub-menu">
                                <form action="{{ route('store.brand.search.page') }}">
                                    <div class="input-search">
                                        <input name="search" type="search" placeholder="{{ trans('base.search_by_brand') }}" autocomplete="off">
                                    </div>
                                </form>
                            </div>
                        </li>
                        <li class="list-inline-item header-main-languages d-none d-md-inline-block d-lg-none">
                            <div class="current-lang link-menu">
                                <span>{{ mb_strtoupper(app()->getLocale()) }}</span>
                                <ul class="sub-menu list-unstyled mb-0 position-absolute py-1">
                                    @foreach(app()->make(\App\Services\Application\ApplicationConfigService::class)->getAvailableLanguages() as $availableLanguage)
                                        @if (mb_strtoupper($availableLanguage) !== mb_strtoupper(app()->getLocale()))
                                            <li>
                                                <a href="{{ route('locale.change', ['newLocale' => mb_strtolower($availableLanguage)]) }}" class="px-6">
                                                    {{ mb_strtoupper($availableLanguage) }}
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="link-menu">{{ trans('base.wallpapers') }}</a>
                        </li>
                        <li class="list-inline-item list-inline-item-menu brand-menu">
                            <div class="link-menu link-menu-sub nolink">{{ trans('base.brands') }}</div>
                            <div class="sub-menu">
                                <div class="row mb-7">
                                    <div class="col-12 col-md-4 col-lg order-1">
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">A</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Anna French</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Anthology</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Architector</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Arte</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">B</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Black Edition</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Borastapeter</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">C</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Casamance</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Casa Mia</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Cole & Son</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Coordonne</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg order-4">
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">D</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">de Gournay</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">E</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Eijffinger</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Etten</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">G</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Gaston y Daniela</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">H</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Harlequin</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">K</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Kirkby Design</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">KT Exclusive</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg order-2">
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">M</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Majvillan</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Mark Alexander</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Matthew Williamson</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Mayflower Wallpaper</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Morris & Co</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Mr Perswall</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Mindthegap</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">N</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Nina Campbell</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Nobilis</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg order-5 mt-md-n19">
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">O</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Osborne & Little</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">P</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Phillip Jefries</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Prestigious Textiles</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Peel & Stick</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">R</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Romo</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">S</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Sanderson</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Scion</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Seabrook</a>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-4 col-lg order-3">
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">T</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Texam home</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Thibaut</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Trend</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">V</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Villa Nova</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Vervain</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">W</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Wallquest</a>
                                            </li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Wallpepper Group</a>
                                            </li>
                                        </ul>
                                        <ul class="list-unstyled mb-5 sub-menu-list">
                                            <li class="font-weight-bold">Z</li>
                                            <li>
                                                <a href="#" class="link-sub-menu">Zoffany</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-center">
                                    <a href="#" class="btn btn-outline-black">{{ trans('base.all_brands') }}</a>
                                </div>
                            </div>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_DELIVERY_AND_PAYMENT)['slug']]) }}" class="link-menu">{{ trans('base.delivery_and_payment') }}</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="link-menu">{{ trans('base.blog') }}</a>
                        </li>
                        <li class="list-inline-item">
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_CONTACTS)['slug']]) }}" class="link-menu">{{ trans('base.contacts') }}</a>
                        </li>
                    </ul>
                    <div class="header-main-others-wrapper align-items-center d-flex d-lg-none">
                        <ul class="list-inline header-main-others mb-0 d-flex flex-column w-100">
                            <li class="list-inline-item">
                                <a href="#" class="i-heart">
                                    <svg>
                                        <use href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                    </svg>
                                    <span>{{ trans('base.favorite') }}</span>
                                </a>
                            </li>
                            <li class="list-inline-item basket-list">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="basket full nolink basket-with-products @if($countOfProductInCart <= 0) d-none @endif">
                                    <span class="after count-of-products-in-basket">{{ $countOfProductInCart }}</span>
                                    <svg class="i-basket-static">
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-basket"></use>
                                    </svg>
                                    <svg class="i-basket-hover">
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-basket-hover"></use>
                                    </svg>
                                    <span>{{ trans('base.cart') }}</span>
                                </a>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.cart.page') }}" class="basket-without-products @if($countOfProductInCart > 0) d-none @endif" rel="noffolow">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-basket-only"></use>
                                    </svg>
                                    <span>{{ trans('base.cart') }}</span>
                                </a>
                            </li>
                            <li class="list-inline-item">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.calculator.page') }}" rel="noffolow">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-calc"></use>
                                    </svg>
                                    <span>{{ trans('base.calculate') }}</span>
                                </a>
                            </li>
                            @auth
                                @if(auth()->user()->isAdmin())
                                    <li class="list-inline-item">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('admin.dashboard.page') }}" rel="noffolow">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-person"></use>
                                            </svg>
                                            <span>{{ trans('user-profile.admin_panel') }}</span>
                                        </a>
                                    </li>
                                @endif
                                <li class="list-inline-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('profile.edit.page') }}" rel="noffolow">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-person"></use>
                                        </svg>
                                        <span>{{ trans('auth.edit_profile') }}</span>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <form action="{{ route('auth.logout') }}" method="POST">
                                        @csrf
                                        <button class="btn btn-link pl-0 pt-0 border-0">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-logout"></use>
                                            </svg>
                                            <span>{{ trans('auth.logout') }}</span>
                                        </button>
                                    </form>
                                </li>
                            @endauth
                            @guest
                                <li class="list-inline-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('auth.sign-in') }}" rel="noffolow">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-person"></use>
                                        </svg>
                                        <span>{{ trans('auth.sign_in') }}</span>
                                    </a>
                                </li>
                            @endguest
                        </ul>
                    </div>
                    <div class="menu-mobile-footer pt-10 d-md-none">
                        <div class="footer-info text-center">
                            <div class="info-text mb-5">
                                {{ trans('base.showroom_line_1') }}<a href="#">{{ trans('base.showroom_address') }}</a>{{ trans('base.showroom_line_2') }}
                                <svg>
                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-watch"></use>
                                </svg>
                                {{ trans('base.showroom_hours') }}
                            </div>
                            <div class="d-flex align-items-center justify-content-center flex-column-reverse">
                                <button type="button" class="btn btn-sm btn-outline-black order-2" data-toggle="modal" data-target="#modal-visit">{{ trans('base.showroom_visit') }}</button>
                                <div class="info-phone mt-6 mb-5">
                                    <a href="tel:+08001234567" class="d-flex align-items-center link-phone justify-content-center justify-content-sm-start">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-phone"></use>
                                        </svg>
                                        0 800 123 45 67
                                    </a>
                                </div>
                            </div>
                        </div>
                        <hr class="m-0">
                        <div class="footer-bottom">
                            <ul class="bottom-socials list-inline text-center mb-0 pt-5">
                                <li class="list-inline-item">
                                    <a href="tel:+" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-instagram"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-twitter"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-facebook"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="#" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-pinterest"></use>
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @yield('content')

    @hasSection('noFooter')
    @else
        <div id="footer" class="footer pb-6 pb-lg-0">
            <div class="container mb-5 mb-lg-8 mb-xl-14">
                <div class="row align-items-center">
                    <div class="col-12 col-lg-7 col-xl-5 order-1">
                        <div class="d-flex d-flex justify-content-center justify-content-lg-between">
                            <div class="footer-logo d-flex justify-content-center justify-content-lg-start mr-lg-4">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">
                                    <img src="{{ Vite::asset('resources/img/logo.svg') }}" alt="footer-logo">
                                </a>
                            </div>
                            <div class="footer-image d-none d-lg-block">
                                <img src="{{ Vite::asset('resources/img/footer-build.jpeg') }}" alt="footer-image">
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-5 col-xl-3 order-3 order-lg-2 mt-8 mt-md-12 mt-lg-0">
                        <div class="footer-info text-center text-lg-left">
                            <div class="info-map mb-2">
                                <svg>
                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-pin"></use>
                                </svg>
                                {{ trans('base.showroom_city') }}
                            </div>
                            <div class="info-text mb-3">
                                {{ trans('base.showroom_line_1') }}<a href="#">{{ trans('base.showroom_address') }}</a>{{ trans('base.showroom_line_2') }}
                                <svg>
                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-watch"></use>
                                </svg>
                                {{ trans('base.showroom_hours') }}
                            </div>
                            <div class="d-flex d-lg-block align-items-center justify-content-center flex-column-reverse flex-md-row">
                                <button type="button" class="btn btn-sm btn-outline-black order-2" data-toggle="modal" data-target="#modal-visit">{{ trans('base.showroom_visit') }}</button>
                                <div class="info-phone mt-7 mt-md-0 mt-lg-3 mr-0 mr-md-7 mr-lg-0 order-1">
                                    <a href="tel:+08001234567" class="d-flex align-items-center link-phone justify-content-center justify-content-sm-start">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-phone"></use>
                                        </svg>
                                        0 800 123 45 67
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-xl-4 mt-6 mt-md-9 mt-lg-8 mt-xl-0 order-2 order-lg-3">
                        <div class="row">
                            <div class="col-6 d-flex justify-content-xl-end">
                                <ul class="list-unstyled footer-menu mb-0  mr-2">
                                    <li>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="link-menu">{{ trans('base.wallpapers') }}</a>
                                    </li>
                                    <li>
                                        <a href="#" class="link-menu">{{ trans('base.sale') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.calculator.page') }}" class="link-menu">{{ trans('base.calculator') }}</a>
                                    </li>
                                    <li>
                                        <a href="#" class="link-menu">{{ trans('base.brands') }}</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="col-6 d-flex justify-content-xl-end">
                                <ul class="list-unstyled footer-menu mb-0 mr-2">
                                    <li>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_FAQ)['slug']]) }}" class="link-menu">{{ trans('base.faq') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}" class="link-menu">{{ trans('base.blog') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_DELIVERY_AND_PAYMENT)['slug']]) }}" class="link-menu">{{ trans('base.delivery_and_payment') }}</a>
                                    </li>
                                    <li>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_CONTACTS)['slug']]) }}" class="link-menu">{{ trans('base.contacts') }}</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="m-0">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="footer-bottom d-flex justify-content-between align-items-center flex-column flex-xl-row pt-lg-6 pb-lg-7">
                            <div class="bottom-left-side d-flex align-items-center order-3 order-xl-1 pt-lg-4 pt-xl-0">
                                <div class="bottom-copyright small order-xl-1 mt-xl-0 mb-xl-0 text-center text-lg-left">
                                    {{ trans('base.copyright') }}
                                </div>
                            </div>
                            <div class="footer-center-side order-2 pt-5 pt-xl-0 d-flex d-lg-block flex-column text-center text-lg-left">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_CONDITIONS)['slug']]) }}" class="link-small mr-lg-8 order-1 order-xl-2 mb-3 mb-md-4 mb-lg-0 mr-0 mr-lg-8">{{ trans('base.conditions') }}</a>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => App\DataClasses\StaticPageTypesDataClass::get(App\DataClasses\StaticPageTypesDataClass::PAGE_POLICY)['slug']]) }}" class="link-small order-2 order-xl-3 mb-3 mb-md-4 mb-lg-0">{{ trans('base.policy') }}</a>
                            </div>
                            <div class="bottom-right-side text-nowrap order-1 order-xl-3 pt-5 pt-md-7 pt-lg-0">
                                <ul class="bottom-socials list-inline mb-0">
                                    <li class="list-inline-item">
                                        <a href="tel:+" class="link-soc" target="_blank" aria-label="instagram">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-instagram"></use>
                                            </svg>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#" class="link-soc" target="_blank" aria-label="twitter">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-twitter"></use>
                                            </svg>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#" class="link-soc" target="_blank" aria-label="facebook">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-facebook"></use>
                                            </svg>
                                        </a>
                                    </li>
                                    <li class="list-inline-item">
                                        <a href="#" class="link-soc" target="_blank" aria-label="pinterest">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-pinterest"></use>
                                            </svg>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <a href="#wrapper" class="btn btn-arrow-up item-scroll position-fixed">
        <svg>
            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow"></use>
        </svg>
    </a>

    <!-- Modal-visit -->
    <div class="modal fade modal-custom" id="modal-visit" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                <div class="left-side">
                    <img src="{{ Vite::asset('resources/img/modal-icon.svg') }}" alt="modal">
                </div>
                <div class="modal-body">
                    <form action="{{ route('store.visit-request.create') }}" method="POST">
                        @csrf
                        <input type="hidden" name="modal_type_id" value="{{ \App\DataClasses\VisitRequestTypeDataClass::SHOWROOM_VISIT }}">
                        <div class="modal-text text-center mb-sm-5">
                            <div class="h3 mb-2">
                                {{ trans('base.showroom_visit') }}
                            </div>
                            <div class="modal-subtext">
                                {{ trans('base.showroom_modal_main_text') }}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                    <label for="name" class="custom-control-label2">{{ trans('base.name') }}</label>
                                    <input type="text" class="form-control" id="name" name="visit_request_name" placeholder="{{ trans('base.your_name') }}" value="{{ old('visit_request_name') }}">
                                    @error('visit_request_name')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="phone" class="custom-control-label2">{{ trans('base.phone') }}</label>
                                    <input type="tel" class="form-control phone" id="phone" name="visit_request_phone" placeholder="+38(0__)___-__-__" value="{{ old('visit_request_phone') }}">
                                    @error('visit_request_phone')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="email" class="custom-control-label2">{{ trans('base.email') }}</label>
                            <input type="text" class="form-control" id="email" name="visit_request_email" placeholder="{{ trans('base.email') }}" value="{{ old('visit_request_email') }}">
                            @error('visit_request_email')
                            <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-12 col-sm-6 mb-3 mb-sm-0">
                                    <label for="date" class="custom-control-label2">{{ trans('base.visit_date') }}</label>
                                    <div class="input-date">
                                        <svg width="21" height="21" viewBox="0 0 21 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M14.806 10.384H15.752C16.434 10.384 16.962 10.934 16.962 11.594C16.962 12.276 16.412 12.804 15.752 12.804H14.806C14.124 12.804 13.596 12.254 13.596 11.594C13.574 10.934 14.124 10.384 14.806 10.384Z" fill="#1D1D23"></path>
                                            <path d="M10.824 10.384H9.878C9.218 10.384 8.668 10.934 8.668 11.594C8.668 12.254 9.196 12.804 9.878 12.804H10.824C11.484 12.804 12.034 12.276 12.034 11.594C12.034 10.934 11.506 10.384 10.824 10.384Z" fill="#1D1D23"></path>
                                            <path d="M4.97199 10.384H5.91799C6.59999 10.384 7.12799 10.934 7.12799 11.594C7.12799 12.276 6.57799 12.804 5.91799 12.804H4.97199C4.28999 12.804 3.76199 12.254 3.76199 11.594C3.73999 10.934 4.28999 10.384 4.97199 10.384Z" fill="#1D1D23"></path>
                                            <path d="M15.752 14.718H14.806C14.124 14.718 13.574 15.268 13.596 15.928C13.596 16.588 14.124 17.138 14.806 17.138H15.752C16.412 17.138 16.962 16.61 16.962 15.928C16.962 15.268 16.434 14.718 15.752 14.718Z" fill="#1D1D23"></path>
                                            <path d="M9.878 14.718H10.824C11.506 14.718 12.034 15.268 12.034 15.928C12.034 16.61 11.484 17.138 10.824 17.138H9.878C9.196 17.138 8.668 16.588 8.668 15.928C8.668 15.268 9.218 14.718 9.878 14.718Z" fill="#1D1D23"></path>
                                            <path d="M5.91799 14.718H4.97199C4.28999 14.718 3.73999 15.268 3.76199 15.928C3.76199 16.588 4.28999 17.138 4.97199 17.138H5.91799C6.57799 17.138 7.12799 16.61 7.12799 15.928C7.12799 15.268 6.59999 14.718 5.91799 14.718Z" fill="#1D1D23"></path>
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.894 1.672H5.214V0.858C5.214 0.396 5.588 0 6.072 0C6.556 0 6.93 0.374 6.93 0.858V1.672H13.794V0.858C13.794 0.396 14.168 0 14.652 0C15.136 0 15.51 0.374 15.51 0.858V1.672H16.83C18.986 1.672 20.724 3.41 20.724 5.566V17.006C20.724 19.162 18.964 20.9 16.83 20.9H3.894C1.738 20.9 0 19.162 0 17.006V5.566C0 3.41 1.76 1.672 3.894 1.672ZM16.83 3.366H15.51V4.18C15.51 4.642 15.136 5.038 14.652 5.038C14.168 5.038 13.794 4.664 13.794 4.18V3.366H6.93V4.18C6.93 4.642 6.556 5.038 6.072 5.038C5.588 5.038 5.214 4.664 5.214 4.18V3.366H3.894C2.684 3.366 1.716 4.356 1.716 5.566V6.688H19.008V5.566C19.008 4.356 18.04 3.366 16.83 3.366ZM3.894 19.184H16.83C18.04 19.184 19.03 18.194 19.03 16.984V8.382H1.716V16.984C1.716 18.216 2.684 19.184 3.894 19.184Z" fill="#1D1D23"></path>
                                        </svg>
                                        <div class="custom-control-number custom-control-number--date">
                                            <div class="before"></div>
                                            <input type="number" class="form-control" min="1" max="31" id="date" name="visit_request_date_day" value="{{ old('visit_request_date_day') }}">
                                            <div class="minus-plus">
                                                <span class="counter minus"><img src="{{ Vite::asset('resources/img/custom-control-number-minus.svg') }}" alt="minus"></span>
                                                <span class="counter plus"><img src="{{ Vite::asset('resources/img/custom-control-number-plus.svg') }}" alt="plus"></span>
                                            </div>
                                        </div>
                                        <div class="custom-control-number custom-control-number--date">
                                            <div class="before"></div>
                                            <input type="number" class="form-control" min="1" max="12" name="visit_request_date_month" value="{{ old('visit_request_date_month') }}">
                                            <div class="minus-plus">
                                                <span class="counter minus"><img src="{{ Vite::asset('resources/img/custom-control-number-minus.svg') }}" alt="minus"></span>
                                                <span class="counter plus"><img src="{{ Vite::asset('resources/img/custom-control-number-plus.svg') }}" alt="plus"></span>
                                            </div>
                                        </div>
                                        <div class="custom-control-number custom-control-number--date">
                                            <div class="before"></div>
                                            <input type="number" class="form-control" min="{{ \Carbon\Carbon::now()->year }}" value="{{ old('visit_request_date_year') ? old('visit_request_date_year') : \Carbon\Carbon::now()->year }}" name="visit_request_date_year">
                                            <div class="minus-plus">
                                                <span class="counter minus"><img src="{{ Vite::asset('resources/img/custom-control-number-minus.svg') }}" alt="minus"></span>
                                                <span class="counter plus"><img src="{{ Vite::asset('resources/img/custom-control-number-plus.svg') }}" alt="plus"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('visit_request_date_day')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    @error('visit_request_date_month')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    @error('visit_request_date_year')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label for="date" class="custom-control-label2">{{ trans('base.visit_time') }}</label>
                                    <div class="input-date">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2ZM12 3.6C7.36081 3.6 3.6 7.36081 3.6 12C3.6 16.6392 7.36081 20.4 12 20.4C16.6392 20.4 20.4 16.6392 20.4 12C20.4 7.36081 16.6392 3.6 12 3.6ZM12.6912 7.45345C12.6555 7.06578 12.3324 6.77201 11.9315 6.78171C11.4998 6.79215 11.1414 7.15058 11.1309 7.58229L11.0174 12.2755L11.0205 12.3665C11.0562 12.7542 11.3793 13.0479 11.7802 13.0382L16.4702 12.9248L16.5615 12.9173C16.9514 12.8628 17.2611 12.5251 17.2708 12.1242L17.2678 12.0332C17.232 11.6455 16.9089 11.3517 16.5081 11.3614L12.5997 11.4568L12.6943 7.54448L12.6912 7.45345Z" fill="#1D1D23"></path>
                                        </svg>
                                        <div class="custom-control-number custom-control-number--time">
                                            {{ trans('base.visit_showroom_from_hours') }}
                                        </div>
                                        <div class="custom-control-number custom-control-number--date">
                                            <div class="before"></div>
                                            <input type="number" class="form-control" min="10" max="20" name="visit_request_time_hours" value="{{ old('visit_request_time_hours') }}">
                                            <div class="minus-plus">
                                                <span class="counter plus"></span>
                                                <span class="counter minus"></span>
                                            </div>
                                        </div>
                                        <div class="custom-control-number custom-control-number--date">
                                            <div class="before"></div>
                                            <input type="number" class="form-control" min="0" max="59" name="visit_request_time_minutes" value="{{ old('visit_request_time_minutes') }}">
                                            <div class="minus-plus">
                                                <span class="counter plus"></span>
                                                <span class="counter minus"></span>
                                            </div>
                                        </div>
                                    </div>
                                    @error('visit_request_time_hours')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                    @error('visit_request_time_minutes')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="wrapper-submit mt-6 mt-sm-8 d-flex justify-content-center">
                            <button type="submit" class="btn btn-dark">{{ trans('base.send') }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal-success -->
    <div class="modal fade modal-custom" id="modal-success" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
                <div class="left-side">
                    <img src="{{ Vite::asset('resources/img/modal-icon.svg') }}" alt="modal">
                </div>
                <div class="modal-body">
                        <div class="modal-text text-center mb-sm-5">
                            <div class="h3 mb-2">
                                {{ trans('base.request_sent_success') }}
                            </div>
                            <div class="modal-subtext">
                                {{ trans('base.request_sent_success_text') }}
                            </div>
                            <div class="pt-5">
                                <button type="button" data-dismiss="modal" aria-label="Close" class="btn btn-dark">{{ trans('base.got_it') }}</button>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- dinamic scripts start --}}
<script>
    const page = '{{ request()->route() ? request()->route()->getName() : '' }}';
    const is_auth = {{ auth()->user() ? 'true' : 'false' }};
    const locale = '{{ app()->getLocale() }}';
    const csrf = '{{ csrf_token() }}';
    const count_of_products_in_cart = {{  $countOfProductInCart }};
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
