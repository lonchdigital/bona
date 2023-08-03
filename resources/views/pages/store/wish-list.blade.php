@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.wish_list') }}</title>
    <meta name="robots" content="noindex, nofollow" />
@endsection

@section('content')
<main class="main wish-list" id="wish-list">
    <div class="content">
        <div class="entry-content">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="wish-list-title h2 d-flex justify-content-lg-center mt-16 mb-2 mt-lg-8 mb-lg-3">{{ trans('base.wish_list') }} (
                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.wishlist.private.page') }}" class="i-heart">
                                <svg>
                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                </svg>
                            </a> )
                        </div>
                    </div>
                </div>
                @if(!count($products))
                    <div class="wish-list-empty mb-16 mb-lg-26 d-block">
                        <div class="row">
                            <div class="col col-xxl-6 mx-auto text-lg-center mb-8">
                                <p class="mb-0 px-xxl-8 pt-2">{{ trans('base.wish_list_description') }}:</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 col-xxl-10 mx-auto">
                                <div class="wish-list-empty-list-wrap p-4 p-xl-11">
                                    <div class="wish-list-empty-list mb-10 mb-xl-21 d-flex flex-column flex-xl-row justify-content-between">
                                        <div class="wish-list-empty-item">
                                            <div class="wish-list-empty-item-wrap d-flex align-items-center mb-5">
                                                <div class="wish-list-empty-item-number d-flex align-items-center justify-content-center mr-3">1</div>
                                                <div class="wish-list-empty-item-title">{{ trans('base.wish_list_find') }}</div>
                                            </div>
                                            <p class="md-3">{{ trans('base.wish_list_how_to_add') }}</p>
                                            <div class="wish-list-empty-item-example">
                                                <div class="wish-list-empty-item-example-item">
                                                    <div class="wish-list-empty-heart">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="wish-list-empty-item-example-item">
                                                    <div class="wish-list-empty-heart">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                                <div class="wish-list-empty-item-example-item">
                                                    <div class="wish-list-empty-heart">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wish-list-empty-item wish-list-empty-item--save">
                                            <div class="wish-list-empty-item-wrap d-flex align-items-center mb-5">
                                                <div class="wish-list-empty-item-number d-flex align-items-center justify-content-center mr-3">2</div>
                                                <div class="wish-list-empty-item-title">{{ trans('base.wish_list_save') }}</div>
                                            </div>
                                            <p class="md-3">{{ trans('base.wish_list_how_to_add_2') }}</p>
                                            <div class="wish-list-empty-item-example">
                                                <div class="wish-list-empty-item-example-item d-flex align-items-center justify-content-center">
                                                    <div class="wish-list-empty-heart">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-heart-hover"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="wish-list-empty-item">
                                            <div class="wish-list-empty-item-wrap d-flex align-items-center mb-5">
                                                <div class="wish-list-empty-item-number d-flex align-items-center justify-content-center mr-3">3</div>
                                                <div class="wish-list-empty-item-title">{{ trans('base.wish_list_calculate') }}</div>
                                            </div>
                                            <p class="md-3">{{ trans('base.wish_list_order_designer_text') }}</p>
                                            <div class="wish-list-empty-item-example">
                                                <div class="wish-list-empty-item-example-item d-flex align-items-center justify-content-center">
                                                    <div class="i-calc">
                                                        <svg>
                                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-calc"></use>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column align-items-center mb-xl-10">
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="btn btn-black-custom btn-add mb-2">
                                            <div class="i-plus mr-2">
                                                <svg>
                                                    <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-plus"></use>
                                                </svg>
                                            </div>
                                            <span>{{ trans('base.wish_list_add_product') }}</span>
                                        </a>
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}" class="btn btn-static-text">{{ trans('base.wish_list_go_to_main') }}</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="wish-list-main row mb-16 mb-lg-26 pt-3 pt-lg-5" id="wish-list-main">
                        <div class="wish-list-left-content col-auto">
                            <div class="wish-list-data" id="wish-list-data">
                                <div class="list-product-table mb-4 mb-lg-13">
                                    @foreach($products as $product)
                                        <div class="list-product-item-wrap">
                                            <div class="row list-product-item">
                                                <div class="col-12 col-xl-6">
                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]) }}" class="table-product d-flex align-items-lg-center">
                                                        <div class="table-product-image mr-3 d-block">
                                                            <img src="{{ $product->main_image_url }}" alt="img">
                                                        </div>
                                                        <div class="table-product-info d-flex flex-row-reverse justify-content-between d-xl-block">
                                                            <div class="table-price text-right d-flex align-items-end d-xl-none">1799 грн.</div>
                                                            <div class="mr-4">
                                                                <div class="table-product-code mb-2">{{ trans('base.sku') }} <span>{{ $product->sku }}</span></div>
                                                                <div class="table-product-name h4 mb-0 d-block" id="test">{{ $product->name }}</div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                                <div class="col-12 col-xl-6">
                                                    <div class="list-product-right d-block d-xl-flex">
                                                        <div class="row align-items-center d-none d-xl-flex">
                                                            <div class="col">
                                                                <div class="table-price text-right">
                                                                    {{ $product->price }} {{ $baseCurrency->name_short }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="row justify-content-between justify-content-sm-end list-product-r-b">
                                                            @if(!$isPublic)
                                                                <div class="col-auto item">
                                                                    <div class="link-wrapper">
                                                                        <input type="hidden" name="slug" value="{{ $product->slug }}">
                                                                        <a href="#" class="link-delete-button wish-list-add-to-cart-button @if($cart->products->contains('id', $product->id)) added @endif">
                                                                            <span class="wrapper-delete-button">
                                                                                <div class="i-basket-only">
                                                                                    <svg>
                                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-basket-only"></use>
                                                                                    </svg>
                                                                                    <span class="ml-2 add-to-cart-text"> @if($cart->products->contains('id', $product->id)) {{ trans('base.in_cart') }} @else {{ trans('base.add_to_cart') }} @endif</span>
                                                                                </div>
                                                                            </span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                                <div class="col-auto item">
                                                                    <div class="link-wrapper">
                                                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.calculator.page', ['productSlug' => $product->slug]) }}" class="link-delete-button">
                                                                            <span class="wrapper-delete-button">
                                                                                <div class="i-calc">
                                                                                    <svg>
                                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-calc"></use>
                                                                                    </svg>
                                                                                    <span class="ml-2">{{ trans('base.wish_list_calculate_wallpaper') }}</span>
                                                                                </div>
                                                                            </span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @if(!$isPublic)
                                                                <div class="col-auto item">
                                                                    <div class="link-wrapper">
                                                                        <a href="#{{ $product->slug }}" class="link-wish-list delete-product-from-wish-list">
                                                                            <span class="wrapper-wish-list">
                                                                                <div class="i-item-delete">
                                                                                    <svg>
                                                                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-item-delete"></use>
                                                                                    </svg>
                                                                                </div>
                                                                                <span class="ml-2">{{ trans('base.wish_list_delete_product') }}</span>
                                                                            </span>
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr class="pb-4">
                                        </div>
                                    @endforeach

                                </div>
                                <div class="wish-list-button-wrap d-none d-lg-flex">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => config('domain.wallpaper_product_type_slug')]) }}" class="btn btn-black-custom mr-2">{{ trans('base.wish_list_go_to_catalog') }}</a>
                                    @if(!$isPublic)
                                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.wishlist.public', ['wishListAccessToken' => $wishList->access_token]) }}" class="btn btn-wish-list-share btn btn-static-text" data-toggle="tooltip" title="" data-original-title="<span class='help'>{{ trans('base.wish_list_share_tooltip') }}</span>">{{ trans('base.wish_list_share') }}</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="wish-list-right-sidebar col-auto">
                            <div id="wish-list-sidebar" class="wish-list-sidebar">
                                <div id="wish-list-sidebar-info" class="wish-list-sidebar-info">
                                    <div class="wish-list-sidebar-item text-center py-4 px-4 px-xxl-10 mb-10 mb-lg-16 ml-xxl-8">
                                        <div class="wish-list-sidebar-item-title mb-5">{{ trans('base.wish_list_use_calculator') }}</div>
                                        <div class="i-calc mb-5">
                                            <svg>
                                                <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-calc"></use>
                                            </svg>
                                        </div>
                                        <a href="#" class="btn btn-black-custom w-100">{{ trans('base.wish_list_go_to_calculator') }}</a>
                                    </div>
                                    <div class="wish-list-sidebar-item text-center py-4 px-4 px-xxl-10 ml-xxl-8">
                                        <div class="wish-list-sidebar-item-title mb-5">{!! trans('base.wish_list_visit_show_room_call_to_action_text', ['SHOWROOM_LINK' => '<a href="#" class="btn p-0 btn-ahead-page">' . trans('base.wish_list_in_showroom') . '</a>']) !!}</div>
                                        <div class="d-flex flex-column">
                                            <button type="button" data-toggle="modal" data-target="#modal-visit" class="btn btn-black-custom mb-2">{{ trans('base.showroom_visit') }}</button>
{{--                                            <button type="button" data-toggle="modal" data-target="#modal-taxi" class="btn btn-black-custom mb-2">{{ trans('base.showroom_visit_taxi') }}</button>--}}
                                            <button type="button" data-toggle="modal" data-target="#modal-designer" class="btn btn-outline-black-custom">{{ trans('base.wish_list_invite_designer') }}</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</main>
<!-- Modal-visit -->
<div class="modal fade modal-custom" id="modal-designer" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn btn-close p-0" data-dismiss="modal" aria-label="Close"></button>
            <div class="left-side">
                <img src="{{ Vite::asset('resources/img/modal-designer.svg') }}" alt="modal">
            </div>
            <div class="modal-body">
                <form action="{{ route('store.visit-request.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="modal_type_id" value="{{ \App\DataClasses\VisitRequestTypeDataClass::DESIGNER_APPOINTMENT }}">
                    <div class="modal-text text-center mb-5">
                        <div class="h3 mb-2 pr-5">
                            {{ trans('base.wish_list_invite_designer') }}
                        </div>
                        <div class="modal-subtext">
                            {{ trans('base.wish_list_invite_designer_text') }}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                <div class="field">
                                    <label for="name" class="custom-control-label2">{{ trans('base.name') }}</label>
                                    <input type="text" class="form-control" id="name" name="visit_request_name" placeholder="{{ trans('base.your_name') }}" value="{{ old('visit_request_name') }}">
                                    @error('visit_request_name')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="field">
                                    <label for="phone" class="custom-control-label2">{{ trans('base.phone') }}</label>
                                    <input type="tel" class="form-control phone" id="phone" name="visit_request_phone" placeholder="+38(0__)___-__-__" value="{{ old('visit_request_phone') }}">
                                    @error('visit_request_phone')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="email" class="custom-control-label2">{{ trans('base.email') }}</label>
                                    <input type="text" class="form-control" id="email" name="visit_request_email" placeholder="{{ trans('base.email') }}" value="{{ old('visit_request_email') }}">
                                    @error('visit_request_email')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                <label for="date" class="custom-control-label2">{{ trans('base.visit_date') }}</label>
                                <div class="input-date">
                                    <svg>
                                        <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-calendar"></use>
                                    </svg>
                                    <div class="custom-control-number custom-control-number--date">
                                        <div class="before"></div>
                                        <input type="number" class="form-control" min="1" max="31" id="date" name="visit_request_date_day" value="{{ old('visit_request_date_day') }}">
                                        <div class="minus-plus">
                                            <span class="counter plus"></span>
                                            <span class="counter minus"></span>
                                        </div>
                                    </div>
                                    <div class="custom-control-number custom-control-number--date">
                                        <div class="before"></div>
                                        <input type="number" class="form-control" min="1" max="12" name="visit_request_date_month" value="{{ old('visit_request_date_month') }}">
                                        <div class="minus-plus">
                                            <span class="counter plus"></span>
                                            <span class="counter minus"></span>
                                        </div>
                                    </div>
                                    <div class="custom-control-number custom-control-number--date">
                                        <div class="before"></div>
                                        <input type="number" class="form-control" min="{{ \Carbon\Carbon::now()->year }}" value="{{ old('visit_request_date_year') ? old('visit_request_date_year') : \Carbon\Carbon::now()->year }}" name="visit_request_date_year">
                                        <div class="minus-plus">
                                            <span class="counter plus"></span>
                                            <span class="counter minus"></span>
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
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                <div class="field">
                                    <label for="address" class="custom-control-label2">{{ trans('base.address') }} <span class="font-weight-normal">{{ trans('base.only_for_kyiv') }}</span></label>
                                    <input type="text" class="form-control" id="address" placeholder="{{ trans('base.address') }}" name="visit_request_address" value="{{ old('visit_request_address') }}">
                                    @error('visit_request_address')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="field">
                                    <label for="parade" class="custom-control-label2">{{ trans('base.entrance_number') }}</label>
                                    <input type="tel" class="form-control" id="parade" placeholder="{{ trans('base.entrance_number') }}" name="visit_request_entrance_number" value="{{ old('visit_request_entrance_number') }}">
                                    @error('visit_request_entrance_number')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="address" class="custom-control-label2">{{ trans('base.comment') }}</label>
                                    <textarea class="form-control h-100" rows="2" placeholder="{{ trans('base.comment') }}" name="visit_request_comment">{{ old('visit_request_comment') }}</textarea>
                                    @error('visit_request_comment')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wrapper-submit mt-6 mt-sm-8 d-flex justify-content-center">
                        <button type="submit" class="btn btn-black-custom">{{ trans('base.send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal-visit -->
<div class="modal fade modal-custom" id="modal-taxi" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <button type="button" class="btn btn-close p-0" data-dismiss="modal" aria-label="Close"></button>
            <div class="left-side">
                <img src="{{ Vite::asset('resources/img/modal-taxi.svg') }}" alt="modal">
            </div>
            <div class="modal-body">
                <form action="{{ route('store.visit-request.create') }}" method="POST">
                    @csrf
                    <input type="hidden" name="modal_type_id" value="{{ \App\DataClasses\VisitRequestTypeDataClass::SHOWROOM_TAXI }}">
                    <div class="modal-text text-center mb-5">
                        <div class="h3 mb-2 pr-5">
                            {{ trans('base.request_taxi_to_showroom') }}
                        </div>
                        <div class="modal-subtext">
                            {{ trans('base.request_taxi_to_showroom_text') }}
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                <div class="field">
                                    <label for="name" class="custom-control-label2">{{ trans('base.name') }}</label>
                                    <input type="text" class="form-control" id="name" name="visit_request_name" placeholder="{{ trans('base.your_name') }}" value="{{ old('visit_request_name') }}">
                                    @error('visit_request_name')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="field">
                                    <label for="phone" class="custom-control-label2">{{ trans('base.phone') }}</label>
                                    <input type="tel" class="form-control phone" id="phone" name="visit_request_phone" placeholder="+38(0__)___-__-__" value="{{ old('visit_request_phone') }}">
                                    @error('visit_request_phone')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-lg-6  mb-3 mb-lg-0">
                                <label for="date" class="custom-control-label2">{{ trans('base.visit_time') }}</label>
                                <div class="input-date">
                                    <div class="d-flex align-items-center">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-watch"></use>
                                        </svg>
                                        <div class="custom-control-number custom-control-number--time">
                                            {{ trans('base.visit_showroom_from_hours') }}
                                        </div>
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
                                        <input type="number" class="form-control" min="00" max="59" name="visit_request_time_minutes" value="{{ old('visit_request_time_minutes') }}">
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
                            <div class="col-12 col-lg-6">
                                <div class="field">
                                    <label class="custom-control-label2">{{ trans('base.city') }}</label>
                                    <input type="text" class="form-control" id="city" placeholder="{{ trans('base.city') }}" name="visit_request_city" value="{{ old('visit_request_city') }}">
                                    @error('visit_request_city')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col-12 col-lg-6 mb-3 mb-lg-0">
                                <div class="field">
                                    <label for="address-taxi" class="custom-control-label2">{{ trans('base.request_taxi_to_showroom_address') }}</label>
                                    <input type="text" class="form-control" id="address-taxi" placeholder="{{ trans('base.request_taxi_to_showroom_address') }}" name="visit_request_address" value="{{ old('visit_request_address') }}">
                                    @error('visit_request_address')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12 col-lg-6">
                                <div class="field">
                                    <label for="parade" class="custom-control-label2">{{ trans('base.entrance_number') }}</label>
                                    <input type="tel" class="form-control" id="parade" placeholder="{{ trans('base.entrance_number') }}" name="visit_request_entrance_number" value="{{ old('visit_request_entrance_number') }}">
                                    @error('visit_request_entrance_number')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="row">
                            <div class="col">
                                <div class="field">
                                    <label for="address" class="custom-control-label2">{{ trans('base.comment') }}</label>
                                    <textarea class="form-control h-100" rows="2" placeholder="{{ trans('base.comment') }}" name="visit_request_comment">{{ old('visit_request_comment') }}</textarea>
                                    @error('visit_request_comment')
                                    <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wrapper-submit mt-6 mt-sm-8 d-flex justify-content-center">
                        <button type="submit" class="btn btn-black-custom">{{ trans('base.send') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
