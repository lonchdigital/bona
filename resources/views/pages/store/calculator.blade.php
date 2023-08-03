@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.online_wallpaper_s_calculator') }}</title>
    <meta name="robots" content="noindex, nofollow" />
@endsection

@section('content')
    <main class="main">
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
                                            {{ trans('base.online_wallpaper_s_calculator') }}
                                            <meta itemprop="position" content="2"/>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="calculator mt-4 mt-lg-11 mb-10 mb-lg-16">
                        <div class=" head-wrap row flex-column-reverse flex-lg-row align-items-center mb-3">
                            <div class="col-12 col-lg-6">
                                <div class="head">{{ trans('base.online_wallpaper_s_calculator') }}</div>
                            </div>
                            <div class="col-12 col-lg-6 text-right mb-10 mb-lg-0">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="btn p-0 btn-ahead-page">{{ trans('base.catalog') }}</a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col col-lg-6">
                                <div class="subhead mb-5">{{ trans('base.online_wallpaper_s_calculator_description') }}</div>
                            </div>
                        </div>
                        <form class="row calculator-main" id="calculator-main-form" action="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.calculator.calculate') }}" method="POST">
                            @csrf
                            <div class="calculator-left-content col-12 col-lg-6 mb-10 mb-lg-0">
                                <div class="calculator-order-data" id="calculator-order-data">
                                    <div class="calculator-item calculator-item-roll mb-10 mb-lg-16">
                                        <div class="row align-items-center pb-2">
                                            <div class="col-12 col-xxxl-6 d-flex align-items-center mb-4 mb-xxxl-0">
                                                <div class="calculator-item-number d-flex align-items-center justify-content-center mr-3">1</div>
                                                <div class="h4 calculator-item-title">{{ trans('base.wallpaper_params') }}
                                                    <div class="i-info d-block d-xxxl-none" data-toggle="tooltip" title="<span class='help'>{{ trans('base.wallpaper_params_description') }}</span>">
                                                        <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-xxxl-6 d-flex align-items-center">
                                                <div class="i-info mr-5 d-none d-xxxl-block" data-toggle="tooltip" title="<span class='help'>{{ trans('base.wallpaper_params_description') }}</span>">
                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                </div>
                                                <div class="filter-item--vendor-code">
                                                    <select class="select-vendor-code" name="product_id">
                                                        @if($preselectedProduct)
                                                            <option value="{{ $preselectedProduct->id }}" selected>{{ $preselectedProduct->sku }}</option>
                                                        @else
                                                            <option value="" selected>{{ trans('base.select_wallpapers') }}</option>
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="calculator-item-content px-lg-3">
                                            <div class="row empty-content">
                                                <div class="col">
                                                    <div class="px-4 py-13">
                                                        <div class="h4 mb-2 text-center">{{ trans('base.nothing_is_selected') }}</div>
                                                        <a href="#" class="btn-ahead mx-auto">
                                                            <span class="mr-2">{{ trans('base.catalog') }}</span>
                                                            <svg>
                                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow"></use>
                                                            </svg>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row filled-content">
                                                @if(auth()->user() && count($wishListProducts))
                                                    <div class="col">
                                                        <div class="wish-list-vendor-code d-flex flex-row-reverse align-items-center mt-4 mb-5">
                                                            <a class="btn collapsed d-flex flex-row-reverse align-items-center p-0" data-toggle="collapse" href="#collapse-wish-list-vendor-code" aria-expanded="false" aria-controls="collapse-wish-list-vendor-code">{{ trans('base.select_from_wishlist') }}</a>
                                                        </div>
                                                        <div class="collapse collapse-wish-list-vendor-code mb-4" id="collapse-wish-list-vendor-code">
                                                            <div class="table-product-wrap d-flex flex-wrap align-items-center">
                                                                @foreach($wishListProducts as $product)
                                                                    <div class="table-product d-flex align-items-center wish-list-product">
                                                                        <input type="hidden" name="id" value="{{ $product->id }}">
                                                                        <input type="hidden" name="sku" value="{{ $product->sku }}">
                                                                        <input type="hidden" name="width" value="{{ $product->width }}">
                                                                        <input type="hidden" name="length" value="{{ $product->length }}">
                                                                        <div class="table-product-image mr-2">
                                                                            <img src="{{ $product->preview_image_url }}" alt="{{ $product->sku }}">
                                                                        </div>
                                                                        <div class="table-product-code">
                                                                            {{ $product->sku }}
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="col">
                                                        <div class="wish-list-vendor-code d-flex flex-row-reverse align-items-center mt-4 mb-5"></div>
                                                    </div>
                                                @endif
                                                    <div class="w-100"></div>
                                                <div class="col-12 col-xxl-6 mb-4 mx-xxl-0 calculator-item-filter">
                                                    <div class="calculator-filter-title mb-3">{{ trans('base.wallpaper_width') }}</div>
                                                    <div class="field">
                                                        <input type="text" class="form-control" id="roll-width" name="wallpaper_width" placeholder="{{ trans('base.enter_wallpaper_width') }}" @if($preselectedProduct) value="{{ $preselectedProduct->width }}" @endif>
                                                    </div>
                                                    <div class="ajaxError text-danger" id="error-field-wallpaper_width"></div>
                                                </div>
                                                <div class="col-12 col-xxl-6 calculator-item-filter">
                                                    <div class="calculator-filter-title mb-3">{{ trans('base.wallpaper_length') }}</div>
                                                    <div class="field">
                                                        <input type="text" class="form-control" id="roll-length" name="wallpaper_length" placeholder="{{ trans('base.enter_wallpaper_length') }}"  @if($preselectedProduct) value="{{ $preselectedProduct->length }}" @endif>
                                                    </div>
                                                    <div class="ajaxError text-danger" id="error-field-wallpaper_length"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="calculator-item calculator-item-room mb-10 mb-lg-16">
                                        <div class="row align-items-center pb-2">
                                            <div class="col col-xxxl-8 d-flex align-items-center">
                                                <div class="calculator-item-number d-flex align-items-center justify-content-center mr-3">2</div>
                                                <div class="h4 calculator-item-title">
                                                    {{ trans('base.room_params') }}
                                                    <div class="i-info d-block d-xxxl-none" data-toggle="tooltip" title="<span class='help'>{{ trans('base.room_params') }}</span>">
                                                        <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="pb-2">
                                        <div class="calculator-item-content px-lg-3">
                                            <div class="row">
                                                <div class="col" id="wall-list">
                                                    <div class="row align-items-center wall" id="wall-0">
                                                        <div class="col-12 col-xxl-4 mb-4">
                                                            <div class="calculator-filter-title">{{ trans('base.wall_height') }}
                                                                <div class="i-info d-md-none" data-toggle="tooltip" title="<span class='help'>{{ trans('base.wall_height') }}</span>">
                                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-xxl-8 mb-4 calculator-item-filter calculator-item-filter-cm">
                                                            <div class="d-flex flex-row-reverse flex-xxl-row align-items-center ml-xxl-7">
                                                                <div class="i-info ml-5 ml-xxl-0 mr-xxl-5 d-none d-md-block" data-toggle="tooltip" title="<span class='help'>{{ trans('base.wall_height') }}</span>">
                                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                                </div>
                                                                <div class="w-100">
                                                                    <div class="field w-100">
                                                                        <input type="text" class="form-control" id="wall-height" name="wall[0][height]">
                                                                    </div>
                                                                    <div class="ajaxError text-danger" id="error-field-wall.0.height"></div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div class="w-100"></div>
                                                        <div class="col-12 col-xxl-4 mb-4">
                                                            <div class="calculator-filter-title">{{ trans('base.wall_width') }}
                                                                <div class="i-info d-md-none" data-toggle="tooltip" title="<span class='help'>{{ trans('base.wall_width') }}</span>">
                                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 col-xxl-8 mb-4 calculator-item-filter calculator-item-filter-cm">
                                                            <div class="d-flex flex-row-reverse flex-xxl-row align-items-center ml-xxl-7">
                                                                <div class="i-info ml-5 ml-xxl-0 mr-xxl-5 d-none d-md-block" data-toggle="tooltip" title="<span class='help'>{{ trans('base.wall_width') }}</span>">
                                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                                </div>
                                                                <div class="w-100">
                                                                    <div class="field w-100">
                                                                        <input type="text" class="form-control" id="wall-width" name="wall[0][width]">
                                                                    </div>
                                                                    <div class="ajaxError text-danger" id="error-field-wall.0.width"></div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-12 mb-4 d-flex justify-content-center">
                                                            <a href="#wall-delete-0" class="link-delete-wall link-delete-button mb--3 wall-delete">
                                                                <span class="wrapper-delete-button">
                                                                    <span class="i-item-delete">
                                                                        <svg>
                                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-item-delete"></use>
                                                                        </svg>
                                                                    </span>
                                                                    <span class="ml-2">{{ trans('base.delete') }}</span>
                                                                </span>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <div class="row align-items-center">
                                                        <div class="w-100"></div>
                                                        <div class="col">
                                                            <button type="button" id="add-wall-button" class="btn btn-add p-0 mx-auto mt-2 mb-4">
                                                                <span class="i-plus mr-md-2">
                                                                    <svg>
                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-plus"></use>
                                                                    </svg>
                                                                </span>
                                                                <span>{{ trans('base.add_wall') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="calculator-item calculator-item-window mb-10 mb-lg-16">
                                        <div class="row align-items-center pb-2">
                                            <div class="col col-xxxl-8 d-flex align-items-center">
                                                <div class="calculator-item-number d-flex align-items-center justify-content-center mr-3">3</div>
                                                <div class="h4 calculator-item-title">
                                                    {{ trans('base.windows_params') }}
                                                    <div class="i-info d-block d-xxxl-none" data-toggle="tooltip" title="<span class='help'>{{ trans('base.windows_params') }}</span>">
                                                        <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 d-none d-xxxl-block">
                                                <div class="i-info ml-auto mr-3" data-toggle="tooltip" title="<span class='help'>{{ trans('base.windows_params') }}</span>">
                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="pb-4 pt-2">
                                        <div class="calculator-item-content px-lg-3">
                                            <div class="row">
                                                <div class="col">
                                                    <div class="row">
                                                        <div class="col" id="windows-list">
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center">
                                                        <div class="col">
                                                            <button type="button" class="btn btn-add p-0 mx-auto mt-7 mb-4" id="add-window-button">
                                                                <span class="i-plus mr-md-2">
                                                                    <svg>
                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-plus"></use>
                                                                    </svg>
                                                                </span>
                                                                <span>{{ trans('base.window_add') }}</span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="calculator-item calculator-item-door">
                                        <div class="row align-items-center pb-2">
                                            <div class="col col-xxxl-8 d-flex align-items-center">
                                                <div class="calculator-item-number d-flex align-items-center justify-content-center mr-3">4</div>
                                                <div class="h4 calculator-item-title">
                                                    {{ trans('base.doors_params') }}
                                                    <div class="i-info d-block d-xxxl-none" data-toggle="tooltip" title="<span class='help'>{{ trans('base.doors_params') }}</span>">
                                                        <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-4 d-none d-xxxl-block">
                                                <div class="i-info ml-auto mr-3" data-toggle="tooltip" title="<span class='help'>{{ trans('base.doors_params') }}</span>">
                                                    <span class="icon-i-info"><span class="path1"></span><span class="path2"></span></span>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="pb-4 pt-2">
                                        <div class="calculator-item-content px-lg-3">
                                            <div class="row">
                                                <div class="col" id="doors-list">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col">
                                                    <button type="button" class="btn btn-add p-0 mx-auto mt-7 mb-4" id="add-door-button">
                                                        <span class="i-plus mr-md-2">
                                                            <svg>
                                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-plus"></use>
                                                            </svg>
                                                        </span>
                                                        <span>{{ trans('base.door_add') }}</span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="right-sidebar col-12 col-lg-6">
                                <div id="calculator-sidebar" class="calculator-sidebar">
                                    <div id="calculator-order-info" class="calculator-order-info">
                                        <h4>{{ trans('base.results') }}</h4>
                                        <hr class="my-4">
                                        <div class="row">
                                            <div class="col" id="selected-product-info">

                                            </div>
                                        </div>
                                        <div class="calculator-order-result flex-column flex-sm-row justify-content-between mb-2 mb-sm-0 d-none" id="results">
                                            <div class="calculator-order-result-item calculator-order-result-roll text-center mb-3 p-5">
                                                <div class="count" id="count-of-rolls"></div>
                                                <div class="descr">{{ trans('base.area_of_rolls') }}</div>
                                            </div>
                                            <div class="calculator-order-result-item calculator-order-result-square-m text-center mb-3 p-5">
                                                <div class="count" id="area-of-rolls"></div>
                                                <div class="descr">{{ trans('base.count_of_rolls') }}</div>
                                            </div>
                                            <div class="calculator-order-result-item calculator-order-result-gluing-area text-center mb-3 p-5">
                                                <div class="count" id="area-required"></div>
                                                <div class="descr">{{ trans('base.area_required') }}</div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col mb-2">
                                                <button type="submit" class="btn btn-black-custom mb-2 mb-md-0 mr-md-2 w-100">{{ trans('base.calculate') }}</button>
                                            </div>
                                        </div>
                                        <div class="calculator-order-button-wrap flex-column flex-md-row d-none" id="buttons">
                                            <input type="hidden" id="selected-product-slug-input" name="slug" value="">
                                            <input type="hidden" id="count-of-rolls-input" name="count_of_rolls" value="">
                                            <button type="button" class="btn btn-black-custom mb-2 mb-md-0 mr-md-2 d-none" id="calculator-add-to-cart">{{ trans('base.add_to_cart') }} ({{ trans('base.sku_short') }} <span class="table-product-code" id="selected-product-sku"></span>)</button>
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.calculator.page') }}" class="btn btn-outline-black-custom btn-calculator-order-clear w-100" id="calculator-reset">{{ trans('base.reset') }}</a>
                                        </div>
                                        <div class="row">
                                            <div class="col text-center mb-2 text-success pt-1" id="calculator-result-success-message"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
