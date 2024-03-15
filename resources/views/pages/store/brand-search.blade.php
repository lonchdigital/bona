@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.search_by_brand') }}</title>
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
                                        <li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brands.list.page') }}">{{ trans('base.brands') }}</a>
                                            <meta itemprop="position" content="2"/>
                                        </li>
                                        <li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem" aria-current="page">
                                            {{ trans('base.search') }}
                                            <meta itemprop="position" content="3"/>
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
                                <div class="head mb-3 mt-10 mt-lg-5">{{ trans('base.search_by_brand') }}</div>
                            </div>
                            <div class="w-100"></div>
                                <div class="col">
                                    <form action="{{ route('store.brand.search.page') }}">
                                        <div class="search-filter mb-10 mb-md-6 mt-n4 mt-md-0">
                                            <div class="input-search">
                                                <input name="search" type="search" placeholder="{{ trans('base.search_by_brand') }}" autocomplete="off" value="{{ $searchText }}"/>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            <div class="w-100"></div>
                            <div class="col">
                                <div class="all-brands-content d-flex flex-wrap">
                                    @foreach($brandsFound as $brand)
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
            </div>
        </div>
    </main>
@endsection
