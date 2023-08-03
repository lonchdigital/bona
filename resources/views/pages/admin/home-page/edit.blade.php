@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.home_page_edit_heading') }}</h2>
                <home-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    collection-search-route="{{ route('admin.collection.search') }}"
                    submit-route="{{ route('admin.home-page.edit') }}"
                    :available-brands="{{ json_encode($brands) }}"
                    :wallpapers-fields="{{ json_encode($fields) }}"
                    @if ($config)
                        :slider-selected-collection="{{ json_encode($config->collection) }}"
                        slider-logo="{{ $config->slider_logo_image_url }}"
                        :wallpapers-by-field-id="{{ $config->product_field_id }}"
                        :slider-title="{{ json_encode($config->getTranslations('slider_title')) }}"
                    @endif

                    @if(count($selectedBrands))
                        :selected-brands="{{ json_encode($selectedBrands->pluck('brand_id')) }}"
                    @endif

                    @if(count($selectedProductFieldOptions))
                        :selected-product-field-options="{{ json_encode($selectedProductFieldOptions->pluck('product_field_option_id')) }}"
                    @endif

                    @if(count($slides))
                        :slider-slides="{{ json_encode($slides) }}"
                    @endif

                />
            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection

