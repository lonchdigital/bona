@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.all_brands_of_wallpapers') }}</title>
@endsection

@section('content')
    <main class="main brands">
        <div class="content">
            <div class="entry-content">
                <div id="b-breadcrumbs" class="b-breadcrumbs">
                    <div class="container">
                        <div class="row">
                            <div class="col mt-4 mt-md-0 mb-4">
                                <nav aria-label="breadcrumb">
                                    <ul class="breadcrumb mb-0" id="breadcrumblist" itemscope itemtype="https://schema.org/BreadcrumbList">
                                        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">Malina Design Studio</a>
                                            <meta itemprop="position" content="1"/>
                                        </li>
                                        <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                                            {{ trans('base.brands') }}
                                            <meta itemprop="position" content="2"/>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div></div>
                <section class="all-brands mb-16">
                    <div class="container">
                        <div class="row">
                            <div class="col text-center">
                                <h1 class="head mb-3 mt-10 mt-lg-5">{{ trans('base.all_brands_of_wallpapers') }}</h1>
                                <div class="subhead mb-6">{{ trans('base.all_brands_of_wallpapers_available_online') }}</div>
                            </div>
                            <div class="w-100"></div>
                            <div class="col col-xxl-10 mx-auto">
                                <div class="d-flex flex-column flex-xxl-row align-items-lg-center justify-content-xxl-center mb-10 mb-lg-14">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brands.list.page', ['letter' => 'all']) }}" class="btn btn-outline-black-custom btn-all-brands py-1 px-5 mb-5 mb-xxl-0 mr-xxl-5">{{ trans('base.all') }}</a>
                                    <ul class="all-brands-list list-unstyled d-flex flex-wrap align-items-center mb-0">
                                        @foreach($brandLetters as $brandLetter => $brands)
                                            <li class="stock @if($brandLetter == $selectedBrandLetter) active @endif"><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brands.list.page', ['letter' => $brandLetter]) }}">{{ $brandLetter }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="w-100"></div>
                            <div class="col">
                                <div class="all-brands-content d-flex flex-wrap">
                                    @foreach($brandsSorted as $brand)
                                        <div class="all-brands-item text-center">
                                            <div class="all-brands-item-inner">
                                                <img src="{{ $brand->logo_image_url }}" alt="img">
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $brand->slug]) }}" class="btn btn-outline-black-custom py-1 px-1 px-xl-5">{{ trans('base.all_collections_of_brand') }}</a>
                                            </div>
                                        </div>
                                    @endforeach
                                    <div class="all-brands-item text-center d-none"></div>
                                    <div class="all-brands-item text-center d-none"></div>
                                    <div class="all-brands-item text-center d-none"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
                @guest
                    @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                        <x-email-subscription-form/>
                    @endif
                @endguest
            </div>
        </div>
    </main>
@endsection
