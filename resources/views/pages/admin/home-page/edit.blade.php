@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.home_page_edit_heading') }}</h2>

{{--                @dd($brands, 'hello')--}}

                <home-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    product-search-route="{{ route('admin.product.list.all') }}"
                    brand-search-route="{{ route('admin.brand.list.all') }}"
                    submit-route="{{ route('admin.home-page.edit') }}"
                    :available-products="{{ json_encode($products) }}"
                    :wallpapers-fields="{{ json_encode($fields) }}"

                    :testimonials-rating-options="{{ json_encode( \App\DataClasses\TestimonialsRatingDataClass::getArray() ) }}"

                    @if($config)
                        :page-meta-title="{{ json_encode($config->getTranslations('meta_title')) }}"
                        :page-meta-description="{{ json_encode($config->getTranslations('meta_description')) }}"
                        :page-meta-keywords="{{ json_encode($config->getTranslations('meta_keywords')) }}"
                        :product-meta-tags="{{ json_encode($config->meta_tags) }}"
                        slider-logo="{{ $config->slider_logo_image_url }}"
                        :wallpapers-by-field-id="{{ $config->product_field_id }}"
                        :slider-title="{{ json_encode($config->getTranslations('slider_title')) }}"
                    @endif

                    @if(count($allProductTypes))
                        :all-product-types="{{ json_encode($allProductTypes) }}"
                    @endif
                    @if( !is_null($config->product_types) )
                        :selected-product-types="{{ $config->product_types }}"
                    @endif

                    @if(count($selectedNewProducts))
                        :selected-new-products="{{ json_encode($selectedNewProducts) }}"
                    @endif

                    @if(count($selectedBestSalesProducts))
                        :selected-best-sales-products="{{ json_encode($selectedBestSalesProducts) }}"
                    @endif

                    @if(count($brands))
                        :selected-brands="{{ json_encode($brands) }}"
                    @endif

                    @if(count($selectedProductFieldOptions))
                        :selected-product-field-options="{{ json_encode($selectedProductFieldOptions->pluck('product_field_option_id')) }}"
                    @endif

                    @if(count($slides))
                        :slider-slides="{{ json_encode($slides) }}"
                    @endif

                    @if(count($testimonials))
                        :testimonial-list="{{ json_encode($testimonials) }}"
                    @endif

                    @if(count($faqs))
                        :faq-list="{{ json_encode($faqs) }}"
                    @endif

                    @if(count($seoText))
                        :seo-title="{{ json_encode($seoText['title']) }}"
                        :seo-text="{{ json_encode($seoText['content']) }}"
                    @endif

                />


            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection

