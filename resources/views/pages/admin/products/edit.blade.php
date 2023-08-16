@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @if(isset($product))
                    <h2 class="page-title">{{ trans('admin.product_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.product_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.product_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
{{--                        @dd($productType->id)--}}
                        <x-admin.reactive-form method="POST"
                                               action="{{ isset($product) ?  route('admin.product.edit', ['productType' => $productType['id'], 'product' => $product->id]) : route('admin.product.create', ['productType' => $productType->id]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- PERMANENT FIELDS START -->

                                    <!-- IS ACTIVE -->
                                    <div class="custom-control custom-checkbox mb-5">
                                        <input type="hidden" name="is_active" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox" id="is_active" name="is_active" @if((isset($product) && $product->is_active) || !isset($product)) checked @endif>
                                        <label class="custom-control-label" for="is_active">{{ trans('admin.product_is_active') }}</label>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-is_active"></div>
                                    </div>

                                    <!-- PARENT PRODUCT -->
                                    <div class="form-group mt-3">
                                        <label for="parent_product_id">{{ trans('admin.parent_product') }}</label>
                                        <select class="form-control select2" name="parent_product_id"
                                                id="parent_product_id">
                                            @if(isset($product) && $product->parent)
                                                <option selected
                                                        value="{{ $product->parent->id }}">{{ $product->parent->name }}</option>
                                            @else
                                                <option selected hidden
                                                        value="">{{ trans('admin.select_product') }}</option>
                                            @endif
                                        </select>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-parent_product_id"></div>
                                    </div>

                                    <!-- NAME -->
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.name')"
                                                                 field-name="name"
                                                                 :values="isset($product) ? $product->getTranslations('name') : []"/>

                                    <!-- SKU -->
                                    <div class="form-group mb-3">
                                        <label for="sku">{{ trans('admin.sku') }} <strong class="text-danger">*</strong></label>
                                        <input type="text" id="sku" name="sku" class="form-control"
                                               @isset($product) value="{{ $product['sku'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-sku"></div>
                                    </div>

                                    <!-- SLUG -->
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control"
                                               @isset($product) value="{{ $product['slug'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                                    </div>

                                    <!-- META TITLE -->
                                    <x-admin.multilanguage-input :is-required="false" :label="trans('admin.meta_title')"
                                                                 field-name="meta_title"
                                                                 :values="isset($product) ? $product->getTranslations('meta_title') : []"/>

                                    <!-- META DESCRIPTION -->
                                    <x-admin.multilanguage-input :is-required="false"
                                                                 :label="trans('admin.meta_description')"
                                                                 field-name="meta_description"
                                                                 :values="isset($product) ? $product->getTranslations('meta_description') : []"/>

                                    <!-- META KEYWORDS -->
                                    <x-admin.multilanguage-input :is-required="false"
                                                                 :label="trans('admin.meta_keywords')"
                                                                 field-name="meta_keywords"
                                                                 :values="isset($product) ? $product->getTranslations('meta_keywords') : []"/>

                                    <!-- STATUS -->
                                    <div class="form-group mt-3">
                                        <label for="availability_status_id">{{ trans('admin.availability_status') }} <strong
                                                class="text-danger">*</strong></label>
                                        <select class="form-control select2" name="availability_status_id" id="availability_status_id">
                                            <option @if(!isset($product)) selected
                                                    @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower(trans('admin.availability_status')) }}</option>
                                            @foreach($productStatuses as $status)
                                                <option
                                                    @if(isset($product) && $product->status_id === $status['id']) selected
                                                    @endif value="{{ $status['id'] }}">{{ $status['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-availability_status_id"></div>
                                    </div>

                                    <!-- SPECIAL OFFERS -->
                                    <div class="form-group mb-3">
                                        <label for="special_offer_id">{{ trans('admin.special_offer') }}</label>
                                        <select multiple class="form-control custom-select2-multi" name="special_offer_id[]" id="special_offer_id">

                                            @foreach(\App\DataClasses\ProductSpecialOfferOptionsDataClass::get() as $specialOffer)
                                                <option
                                                    @if(isset($product) && $product->special_offers && in_array($specialOffer['id'], $product->special_offers ))
                                                        selected
                                                    @endif
                                                    value="{{ $specialOffer['id'] }}">

                                                    {{ $specialOffer['internal_name'] }}

                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-special_offer_id"></div>
                                    </div>

                                    <!-- SALE PRICE -->
                                    <div class="form-group mb-3">
                                        <label for="old_price">{{ trans('admin.old_price_in_currency') }}
                                            ({{ trans('admin.old_price_in_currency_description') }})</label>
                                        <div class="input-group">
                                            <input type="text" id="old_price_in_currency" name="old_price_in_currency"
                                                   class="form-control input-money"
                                                   aria-describedby="sale-price-currency"
                                                   @isset($product) value="{{ number_format($product['old_price_in_currency'], 2, '.', '') }}" @endisset>
                                        </div>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-old_price"></div>
                                    </div>

                                    <!-- PRICE IN CURRENCY -->
                                    <div class="form-group mb-3">
                                        <label for="price_in_currency">{{ trans('admin.price_in_currency') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="price_in_currency" name="price_in_currency"
                                               class="form-control input-money"
                                               @isset($product) value="{{ number_format($product['price_in_currency'], 2, '.', '') }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-price_in_currency"></div>
                                    </div>

                                    <!-- PURCHASE PRICE IN CURRENCY -->
                                    <div class="form-group mb-3">
                                        <label for="price_in_currency">{{ trans('admin.purchase_price_in_currency') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="purchase_price_in_currency" name="purchase_price_in_currency"
                                               class="form-control input-money"
                                               @isset($product) value="{{ number_format($product['purchase_price_in_currency'], 2, '.', '') }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-purchase_price_in_currency"></div>
                                    </div>

                                    <!-- PRICE IN CURRENCY CURRENCY ID -->
                                    <div class="form-group mb-3">
                                        <label for="currency_id">{{ trans('admin.price_currency') }} <strong
                                                class="text-danger">*</strong></label>
                                        <select class="form-control select2" name="currency_id" id="currency_id">
                                            <option @if(!isset($product)) selected
                                                    @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower(trans('admin.price_currency')) }}</option>
                                            @foreach($currencies as $currency)
                                                    <option
                                                        @if(isset($product) && $product->price_currency_id === $currency->id) selected
                                                        @endif value="{{ $currency->id }}">{{ $currency->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-currency_id"></div>
                                    </div>

                                    <!-- MAIN IMAGE -->
                                    <div class="form-group mb-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="main_image">{{ trans('admin.product_main_image') }} <strong
                                                        class="text-danger">*</strong>
                                                    ({{ trans('admin.product_main_image_requirements') }}, jpeg,png,jpg)</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <img
                                                    @if(isset($product) && isset($product->main_image_url)) src="{{ $product->main_image_url }}"
                                                    @else style="display: none;" @endif id="main_image_preview"
                                                    alt="{{ trans('admin.product_main_image') }}"
                                                    class="category-img rounded mb-3">
                                            </div>
                                        </div>
                                        <div class="row mb-3">
                                            <div class="col-md-2">
                                                <div class="custom-file">
                                                    <input type="hidden" name="main_image_deleted_input"
                                                           id="main_image_deleted_input" value="0">
                                                    <input type="file" class="custom-file-input image-input"
                                                           name="main_image" id="main_image">
                                                    <label class="custom-file-label"
                                                           for="main_image">{{ trans('admin.choose_file') }}</label>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-main_image"></div>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-main_image_deleted_input"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-2">
                                                <a @if(!(isset($product) && isset($product->pattern_image_url))) style="display: none;"
                                                   @endif href="#" id="main_image_delete"
                                                   class="btn btn-danger w-100"><span
                                                        class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-4 col-lg-3 col-xl-2">
                                            <!-- PATTERN IMAGE -->
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            for="pattern_image">{{ trans('admin.product_image_pattern') }}
                                                            <strong class="text-danger">*</strong>
                                                            ({{ trans('admin.product_image_pattern_requirements') }},
                                                            jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <img
                                                            @if(isset($product) && isset($product->pattern_image_url)) src="{{ $product->pattern_image_url }}"
                                                            @else style="display: none;"
                                                            @endif id="pattern_image_preview"
                                                            alt="{{ trans('admin.product_image_pattern') }}"
                                                            class="category-img rounded mb-3 preview-image">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="hidden" name="pattern_image_deleted_input"
                                                                   id="pattern_image_deleted_input" value="0">
                                                            <input type="file" class="custom-file-input image-input"
                                                                   name="pattern_image" id="pattern_image">
                                                            <label class="custom-file-label"
                                                                   for="pattern_image">{{ trans('admin.choose_file') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a @if(!(isset($product) && isset($product->pattern_image_url))) style="display: none;"
                                                           @endif href="#" id="pattern_image_delete"
                                                           class="btn btn-danger w-100"><span
                                                                class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-3 col-xl-2">
                                            <!-- GALLERY IMAGE 1 -->
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            for="gallery_image_1">{{ trans('admin.product_gallery_image_1') }}
                                                            ({{ trans('admin.product_gallery_requirements') }},
                                                            jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <img
                                                            @if(isset($product) && isset($product->gallery_image_1_url)) src="{{ $product->gallery_image_1_url }}"
                                                            @else style="display: none;"
                                                            @endif id="gallery_image_1_preview"
                                                            alt="{{ trans('admin.product_gallery_image_1') }}"
                                                            class="category-img rounded mb-3">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="hidden" name="gallery_image_1_deleted_input"
                                                                   id="gallery_image_1_deleted_input" value="0">
                                                            <input type="file" class="custom-file-input image-input"
                                                                   name="gallery_image_1" id="gallery_image_1">
                                                            <label class="custom-file-label"
                                                                   for="gallery_image_1">{{ trans('admin.choose_file') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a @if(!(isset($product) && isset($product->gallery_image_1_url))) style="display: none;"
                                                           @endif href="#" id="gallery_image_1_delete"
                                                           class="btn btn-danger w-100"><span
                                                                class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-3 col-xl-2">
                                            <!-- GALLERY IMAGE 2 -->
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            for="gallery_image_2">{{ trans('admin.product_gallery_image_2') }}
                                                            ({{ trans('admin.product_gallery_requirements') }},
                                                            jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <img
                                                            @if(isset($product) && isset($product->gallery_image_2_url)) src="{{ $product->gallery_image_2_url }}"
                                                            @else style="display: none;"
                                                            @endif id="gallery_image_2_preview"
                                                            alt="{{ trans('admin.product_gallery_image_2') }}"
                                                            class="category-img rounded mb-3">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="hidden" name="gallery_image_2_deleted_input"
                                                                   id="gallery_image_2_deleted_input" value="0">
                                                            <input type="file" class="custom-file-input image-input"
                                                                   name="gallery_image_2" id="gallery_image_2">
                                                            <label class="custom-file-label"
                                                                   for="gallery_image_2">{{ trans('admin.choose_file') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a @if(!(isset($product) && isset($product->gallery_image_2_url))) style="display: none;"
                                                           @endif href="#" id="gallery_image_2_delete"
                                                           class="btn btn-danger w-100"><span
                                                                class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-3 col-xl-2">
                                            <!-- GALLERY IMAGE 3 -->
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            for="gallery_image_3">{{ trans('admin.product_gallery_image_3') }}
                                                            ({{ trans('admin.product_gallery_requirements') }},
                                                            jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <img
                                                            @if(isset($product) && isset($product->gallery_image_3_url)) src="{{ $product->gallery_image_3_url }}"
                                                            @else style="display: none;"
                                                            @endif id="gallery_image_3_preview"
                                                            alt="{{ trans('admin.product_gallery_image_2') }}"
                                                            class="category-img rounded mb-3">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="hidden" name="gallery_image_3_deleted_input"
                                                                   id="gallery_image_3_deleted_input" value="0">
                                                            <input type="file" class="custom-file-input image-input"
                                                                   name="gallery_image_3" id="gallery_image_3">
                                                            <label class="custom-file-label"
                                                                   for="gallery_image_3">{{ trans('admin.choose_file') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a @if(!(isset($product) && isset($product->gallery_image_3_url))) style="display: none;"
                                                           @endif href="#" id="gallery_image_3_delete"
                                                           class="btn btn-danger w-100"><span
                                                                class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-3 col-xl-2">
                                            <!-- GALLERY IMAGE 4 -->
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            for="gallery_image_4">{{ trans('admin.product_gallery_image_4') }}
                                                            ({{ trans('admin.product_gallery_requirements') }},
                                                            jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <img
                                                            @if(isset($product) && isset($product->gallery_image_4_url)) src="{{ $product->gallery_image_4_url }}"
                                                            @else style="display: none;"
                                                            @endif id="gallery_image_4_preview"
                                                            alt="{{ trans('admin.product_gallery_image_4') }}"
                                                            class="category-img rounded mb-3">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="hidden" name="gallery_image_4_deleted_input"
                                                                   id="gallery_image_4_deleted_input" value="0">
                                                            <input type="file" class="custom-file-input image-input"
                                                                   name="gallery_image_4" id="gallery_image_4">
                                                            <label class="custom-file-label"
                                                                   for="gallery_image_4">{{ trans('admin.choose_file') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a @if(!(isset($product) && isset($product->gallery_image_4_url))) style="display: none;"
                                                           @endif href="#" id="gallery_image_4_delete"
                                                           class="btn btn-danger w-100"><span
                                                                class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-lg-3 col-xl-2">
                                            <!-- GALLERY IMAGE 5 -->
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label
                                                            for="gallery_image_5">{{ trans('admin.product_gallery_image_5') }}
                                                            ({{ trans('admin.product_gallery_requirements') }},
                                                            jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <img
                                                            @if(isset($product) && isset($product->gallery_image_5_url)) src="{{ $product->gallery_image_5_url }}"
                                                            @else style="display: none;"
                                                            @endif id="gallery_image_5_preview"
                                                            alt="{{ trans('admin.product_gallery_image_5') }}"
                                                            class="category-img rounded mb-3">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="hidden" name="gallery_image_5_deleted_input"
                                                                   id="gallery_image_5_deleted_input" value="0">
                                                            <input type="file" class="custom-file-input image-input"
                                                                   name="gallery_image_5" id="gallery_image_5">
                                                            <label class="custom-file-label"
                                                                   for="gallery_image_5">{{ trans('admin.choose_file') }}</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <a @if(!(isset($product) && isset($product->gallery_image_5_url))) style="display: none;"
                                                           @endif href="#" id="gallery_image_5_delete"
                                                           class="btn btn-danger w-100"><span
                                                                class="fe fe-trash-2 fe-16 mr-2"></span>{{ trans('admin.delete') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-pattern_image"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-pattern_image_deleted_input"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-gallery_image_1"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-gallery_image_2"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-gallery_image_3"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-gallery_image_4"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-gallery_image_5"></div>
                                        </div>
                                    </div>

                                    <!-- COUNTRY -->
                                    <div class="form-group mb-3">
                                        <label for="country_id">{{ trans('admin.country') }} <strong
                                                class="text-danger">*</strong></label>
                                        <select class="form-control select2" name="country_id" id="country_id">
                                            <option @if(!isset($product)) selected
                                                    @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower(trans('admin.country')) }}</option>
                                            @foreach($countries as $country)
                                                <option
                                                    @if(isset($product) && $product->country_id === $country->id) selected
                                                    @endif data-image="{{ $country->image_url }}"
                                                    value="{{ $country->id }}">{{ $country->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-country_id"></div>
                                    </div>

                                    <!-- PERMANENT FIELDS END -->

                                    <!-- CUSTOM FIELDS START -->

                                    @if($productType->has_brand || $productType->has_collection)
                                        <!-- BRAND -->
                                        <div class="form-group mb-3">
                                            <label for="brand_id">{{ trans('admin.brand') }} <strong
                                                    class="text-danger">*</strong></label>
                                            <select class="form-control select2" name="brand_id" id="brand_id">
                                                <option @if(!isset($product)) selected
                                                        @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower(trans('admin.brand')) }}</option>
                                                @foreach($brands as $brand)
                                                    <option
                                                        @if(isset($product) && $product->brand_id === $brand->id) selected
                                                        @endif value="{{ $brand->id }}">{{ $brand->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-brand_id"></div>
                                        </div>
                                    @endif

                                    @if($productType->has_collection)
                                        <!-- COLLECTION -->
                                        <div class="form-group mb-3">
                                            <label for="collection_id">{{ trans('admin.collection') }} <strong
                                                    class="text-danger">*</strong></label>
                                            <select class="form-control select2" name="collection_id"
                                                    id="collection_id">
                                                <option @if(!isset($product)) selected
                                                        @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower(trans('admin.collection')) }}</option>
                                                @if(isset($product) && $product->collection_id)
                                                    <option selected
                                                            value="{{ $product->collection_id }}">{{ $product->collection->name }}</option>
                                                @endif
                                            </select>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-collection_id"></div>
                                        </div>
                                    @endif

                                    @if($productType->has_category)
                                        <!-- CATEGORY -->
                                        <div class="form-group mb-3">
                                            <label for="category_ids">{{ trans('admin.product_categories') }} <strong
                                                    class="text-danger">*</strong></label>
                                            <select multiple class="form-control select2-multi custom-select2-multi"
                                                    name="category_ids[]" id="category_ids">
                                                @foreach($categories as $category)
                                                    <option
                                                        @if(isset($product) && $product->categories->contains('id', $category->id)) selected
                                                        @endif value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-category_ids"></div>
                                        </div>
                                    @endif

                                    @if($productType->has_color)
                                        <!-- MAIN COLOR -->
                                        <div class="form-group mb-3">
                                            <label for="color_id">{{ trans('admin.color') }} <strong
                                                    class="text-danger">*</strong></label>
                                            <select class="form-control select2" name="color_id" id="color_id">
                                                <option @if(!isset($product)) selected
                                                        @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower(trans('admin.color')) }}</option>
                                                @foreach($colors as $color)
                                                    <option
                                                        @if(isset($product) && $product->main_color_id === $color->id) selected
                                                        @endif data-value="{{ $color->hex }}"
                                                        value="{{ $color->id }}">{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-color_id"></div>
                                        </div>

                                        <!-- ALL COLORS -->
                                        <div class="form-group mb-3">
                                            <label for="all_color_ids">{{ trans('admin.all_colors') }} <strong
                                                    class="text-danger">*</strong></label>
                                            <select multiple class="form-control select2" name="all_color_ids[]"
                                                    id="all_color_ids">
                                                @foreach($colors as $color)
                                                    <option
                                                        @if(isset($product) && $product->colors->contains('id', $color->id)) selected
                                                        @endif data-value="{{ $color->hex }}"
                                                        value="{{ $color->id }}">{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-all_color_ids"></div>
                                        </div>
                                    @endif

                                    <!-- SIZES -->
                                    @if($productType->has_size)
                                        @if($productType->has_length)
                                            <div class="form-group mb-3">
                                                <label for="product-length">{{ trans('admin.length') }} @if(!$productType->has_height) / {{ trans('admin.height') }} @endif <strong
                                                        class="text-danger">*</strong></label>
                                                <div class="input-group">
                                                    <input type="text" id="product-length" name="length"
                                                           class="form-control" aria-describedby="product-size-points"
                                                           @if(isset($product)) value="{{ $product->length }}" @endif>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"
                                                              id="product-size-points">{{ $productType->size_points }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-danger ajaxError" id="error-field-length"></div>
                                            </div>
                                        @endif
                                        @if($productType->has_width)
                                            <div class="form-group mb-3">
                                                <label for="product-width">{{ trans('admin.width') }} <strong
                                                        class="text-danger">*</strong></label>
                                                <div class="input-group">
                                                    <input type="text" id="product-width" name="width"
                                                           class="form-control" aria-describedby="product-size-points"
                                                           @if(isset($product)) value="{{ $product->width }}" @endif>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"
                                                              id="product-size-points">{{ $productType->size_points }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-danger ajaxError" id="error-field-width"></div>
                                            </div>
                                        @endif
                                        @if($productType->has_height)
                                            <div class="form-group mb-3">
                                                <label for="product-height">{{ trans('admin.height') }} <strong
                                                        class="text-danger">*</strong></label>
                                                <div class="input-group">
                                                    <input type="text" id="product-height" name="height"
                                                           class="form-control" aria-describedby="product-size-points"
                                                           @if(isset($product)) value="{{ $product->height }}" @endif>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"
                                                              id="product-size-points">{{ $productType->size_points }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-danger ajaxError" id="error-field-height"></div>
                                            </div>
                                        @endif
                                    @endif

                                    <!-- CUSTOM FIELDS -->
                                    @foreach($productType->fields as $customField)
                                        @if($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING)
                                            <div class="form-group mb-3">
                                                <label
                                                    for="custom-field-{{$customField->id}}">{{ $customField->field_name }}
                                                    <strong class="text-danger">*</strong></label>
                                                <input type="hidden" name="custom_field[{{$customField->id}}][field_id]"
                                                       value="{{$customField->id}}">
                                                <input type="text" id="custom-field-{{$customField->id}}"
                                                       name="custom_field[{{$customField->id}}][value]"
                                                       class="form-control"
                                                       @if(isset($product)) value="{{ $product->getCustomFieldValue($customField->id) ?? '' }}" @endif>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-custom_field.{{$customField->id}}.value"></div>
                                            </div>
                                        @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER)
                                            <div class="form-group mb-3">
                                                <label
                                                    for="custom-field-{{$customField->id}}">{{ $customField->field_name }}
                                                    <strong class="text-danger">*</strong></label>
                                                <input type="hidden" name="custom_field[{{$customField->id}}][field_id]"
                                                       value="{{$customField->id}}">
                                                <input type="text" id="custom-field-{{$customField->id}}"
                                                       name="custom_field[{{$customField->id}}][value]"
                                                       class="form-control"
                                                       @if(isset($product)) value="{{ $product->getCustomFieldValue($customField->id) ?? '' }}" @endif>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-custom_field.{{$customField->id}}.value"></div>
                                            </div>
                                        @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                                            <div class="form-group mb-3">
                                                <label
                                                    for="custom-field-{{$customField->id}}">{{ $customField->field_name }}
                                                    <strong class="text-danger">*</strong></label>
                                                <input type="hidden" name="custom_field[{{$customField->id}}][field_id]"
                                                       value="{{$customField->id}}">
                                                <div class="input-group">
                                                    <input type="text" id="custom-field-{{$customField->id}}"
                                                           name="custom_field[{{$customField->id}}][value]"
                                                           class="form-control"
                                                           aria-describedby="custom-field-{{$customField->id}}"
                                                           @if(isset($product)) value="{{ $product->getCustomFieldValue($customField->id) ?? '' }}" @endif>
                                                    <div class="input-group-append">
                                                        <span class="input-group-text"
                                                              id="custom-field-{{$customField->id}}">{{ $customField->field_size_name }}</span>
                                                    </div>
                                                </div>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-custom_field.{{$customField->id}}.value"></div>
                                            </div>
                                        @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                            @if($customField->is_multiselectable)
                                                <div class="form-group">
                                                    <label
                                                        for="custom-field-{{ $customField->id }}">{{ $customField->field_name }}
                                                        <strong class="text-danger">*</strong></label>
                                                    <input type="hidden"
                                                           name="custom_field[{{$customField->id}}][field_id]"
                                                           value="{{$customField->id}}">
                                                    <select multiple
                                                            class="form-control select2-multi custom-select2-multi"
                                                            name="custom_field[{{$customField->id}}][value][]"
                                                            id="custom-field-{{ $customField->id }}">
                                                        @foreach($customField->options as $option)
                                                            <option
                                                                @if(isset($product) && count($product->getCustomFieldValue($customField->id)) && in_array($option->id, $product->getCustomFieldValue($customField->id))) selected
                                                                @endif value="{{ $option->id }}">{{ $option->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-custom_field.{{$customField->id}}.value"></div>
                                                </div>
                                            @else
                                                <div class="form-group">
                                                    <label
                                                        for="custom-field-{{ $customField->id }}">{{ $customField->field_name }}
                                                        <strong class="text-danger">*</strong></label>
                                                    <input type="hidden"
                                                           name="custom_field[{{$customField->id}}][field_id]"
                                                           value="{{$customField->id}}">
                                                    <select class="form-control select2 custom-select2"
                                                            name="custom_field[{{$customField->id}}][value]"
                                                            id="custom-field-{{ $customField->id }}">
                                                        <option @if(!isset($product)) selected
                                                                @endif disabled>{{ trans('admin.select') }} {{ mb_strtolower($customField->field_name) }}</option>
                                                        @foreach($customField->options as $option)
                                                            <option
                                                                @if(isset($product) && $product->getCustomFieldValue($customField->id) == $option->id) selected
                                                                @endif value="{{ $option->id }}">{{ $option->name }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-custom_field.{{$customField->id}}.value"></div>
                                                </div>
                                            @endif
                                        @endif
                                    @endforeach

                                    <!-- CUSTOM FIELDS END -->


                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.product.list.page', ['productType' => $productType->id]) }}"
                                       class="btn btn-secondary">{{ trans('admin.back') }}</a>
                                    <button type="submit" class="btn btn-dark">{{ trans('admin.save') }}</button>
                                </div>
                            </div>
                        </x-admin.reactive-form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script src="/static-admin/js/cyrillic-lat-conventer.js"></script>
    <script src="/static-admin/js/jquery-helpers.js"></script>
    <script type='text/javascript'>
        $(document).ready(function () {
            loadImagesHandlers();
            loadSlugifyFields();
            loadSelect2Dropdowns();
            loadCollectionsByBrand($('#brand_id').val());

        });

        function loadImagesHandlers() {
            $('.image-input').each(function () {
                const id = $(this).attr('id');
                const previewId = id + '_preview';
                const deleteButtonId = id + '_delete';
                const deletedInputId = id + '_deleted_input';

                const input = $(this);
                const previewImage = $('#' + previewId);
                const deleteButton = $('#' + deleteButtonId);
                const deletedInput = $('#' + deletedInputId);

                $(this).change(function () {
                    const [file] = $(this).prop('files');
                    if (file) {
                        previewImage.attr('src', URL.createObjectURL(file)).attr('style', '');
                        deleteButton.attr('style', '');
                        deletedInput.val(0);
                    }
                });

                deleteButton.click(function (event) {
                    event.preventDefault();
                    previewImage.attr('src', '').attr('style', 'display: none;');
                    $(this).attr('style', 'display: none;');
                    input.val();
                    deletedInput.val(1);
                });
            });
        }

        function loadSlugifyFields() {
            let CyrLat = new CyrLatConverter().init();

            const nameField = $('#name_{{$baseLanguage}}');
            const skuField = $('#sku');

            nameField.keyup(function () {
                const value = $(this).val() + '-' + skuField.val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue));
            });

            skuField.keyup(function () {
                const value = nameField.val() + '-' + $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue));
            });
        }

        function loadSelect2Dropdowns() {
            $('#availability_status_id, #currency_id').select2({
                theme: 'bootstrap4',
            });

            $('#country_id').select2({
                templateResult: formatStateCountry,
                templateSelection: formatStateCountry,
                theme: 'bootstrap4',
            });

            $('#color_id').select2({
                templateResult: formatStateColor,
                templateSelection: formatStateColor,
                theme: 'bootstrap4',
            });

            $('#all_color_ids').select2({
                multiple: true,
                templateResult: formatStateColor,
                templateSelection: formatStateColor,
                theme: 'bootstrap4',
            });

            $('#brand_id').change(function () {
                const brand_id = $(this).val();

                if (brand_id) {
                    loadCollectionsByBrand(brand_id);
                    $('#collection_id').val(null).trigger('change');
                }
            }).select2({
                theme: 'bootstrap4',
            });

            $('.input-money').mask("###0.00", {
                reverse: true
            });

            $('.custom-select2').each(function () {
                $(this).select2({
                    theme: 'bootstrap4',
                });
            })

            $('.custom-select2-multi').select2({
                multiple: true,
                theme: 'bootstrap4',
                placeholder: '{{ trans('admin.select') }}'
            });

            $('#parent_product_id').select2({
                theme: 'bootstrap4',
                ajax: {
                    url: '{{ route('admin.product.list', ['productType' => $productType->id]) }}',
                    type: 'get',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        response.data.unshift({
                            id: '',
                            text: '{{ trans('admin.select_product') }}',
                        });
                        return {
                            results: response.data
                        };
                    },
                    cache: true
                },
            }).on('select2:select', function (event) {
                getParentProductData(event.params.data.id);
            });
        }

        function loadCollectionsByBrand(brandId) {
            $('#collection_id').select2({
                theme: 'bootstrap4',
                ajax: {
                    url: '{{ route('admin.brand.collections.list', ['brand' => 'BRAND_ID']) }}'.replace('BRAND_ID', brandId),
                    type: 'get',
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response.data
                        };
                    },
                    cache: true
                },
            });
        }

        function getParentProductData(productId)
        {
            $.ajax({
                url: '{{ route('admin.product.parent', ['productType' => $productType->id, 'product' => 'PRODUCT_ID']) }}'.replace('PRODUCT_ID', productId),
                type: 'get',
                dataType: 'json',
                success: function (data) {
                    prePopulateFormData(data.data);
                },
            });
        }

        function prePopulateFormData(data)
        {
            if (data.hasOwnProperty('price')) {
                $('#price').val(data.price.toFixed(2));
            }

            if (data.hasOwnProperty('old_price') && data.old_price) {
                $('#old_price').val(data.old_price.toFixed(2));
            }

            if (data.hasOwnProperty('meta_title')) {
                Object.keys(data.meta_title).forEach(lang => {
                    $('#meta_title_' + lang).val(data.meta_title[lang]);
                });
            }

            if (data.hasOwnProperty('meta_description')) {
                Object.keys(data.meta_description).forEach(lang => {
                    $('#meta_description_' + lang).val(data.meta_description[lang]);
                });
            }

            if (data.hasOwnProperty('meta_keywords')) {
                Object.keys(data.meta_keywords).forEach(lang => {
                    $('#meta_keywords_' + lang).val(data.meta_keywords[lang]);
                });
            }

            if (data.hasOwnProperty('special_offers') && data.special_offers) {
                $('#special_offer_id').val(data.special_offers).trigger('change');
            }

            if (data.hasOwnProperty('price_in_currency')) {
                $('#price_in_currency').val(data.price_in_currency.toFixed(2));
            }

            if (data.hasOwnProperty('price_currency_id')) {
                $('#currency_id').val(data.price_currency_id).trigger('change');
            }

            if (data.hasOwnProperty('country_id')) {
                $('#country_id').val(data.country_id).trigger('change');
            }

            if (data.hasOwnProperty('brand_id')) {
                $('#brand_id').val(data.brand_id).trigger('change');
            }

            if (data.hasOwnProperty('categories')) {
                $('#category_ids').val(data.categories).trigger('change');
            }

            if (data.hasOwnProperty('main_color_id')) {
                $('#color_id').val(data.main_color_id).trigger('change');
            }

            if (data.hasOwnProperty('all_color_ids')) {
                $('#all_color_ids').val(data.all_color_ids).trigger('change');
            }

            if (data.hasOwnProperty('length') && data.length) {
                $('#product-length').val(data.length);
            }

            if (data.hasOwnProperty('width') && data.width) {
                $('#product-width').val(data.width);
            }

            if (data.hasOwnProperty('height') && data.height) {
                $('#product-height').val(data.height);
            }

            if (data.hasOwnProperty('collection')) {
                if ($('#collection_id').find("option[value='" + data.collection.id + "']").length) {
                    $('#collection_id').val(data.id).trigger('change');
                } else {
                    var newOption = new Option(data.collection.text, data.collection.id, true, true);
                    $('#collection_id').append(newOption).trigger('change');
                }
            }

            if (data.hasOwnProperty('custom_fields')) {
                data.custom_fields.forEach(function (item) {
                    $('#custom-field-' + item.field_id).val(item.value).trigger('change');
                });
            }
        }
    </script>
@endpush

