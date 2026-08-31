@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.home_page_edit_heading') }}</h2>

                <home-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    product-search-route="{{ route('admin.product.list.all') }}"
                    brand-search-route="{{ route('admin.brand.list.all') }}"
                    instagram-auth-route="{{ route('admin.instagram.auth') }}"
                    submit-route="{{ route('admin.home-page.edit') }}"
                    :style-section="{{ json_encode($styleSection) }}"
                    :content-sections="{{ json_encode($contentSections) }}"

                    :testimonials-rating-options="{{ json_encode( \App\DataClasses\TestimonialsRatingDataClass::getArray() ) }}"

                    @if($config)
                        :page-meta-title="{{ json_encode($config->getTranslations('meta_title')) }}"
                        :page-meta-description="{{ json_encode($config->getTranslations('meta_description')) }}"
                        :page-meta-keywords="{{ json_encode($config->getTranslations('meta_keywords')) }}"
                        :product-meta-tags="{{ json_encode($config->meta_tags) }}"
                    @endif

                    @if(count($allCatalogOptions))
                        :all-catalog-options="{{ json_encode($allCatalogOptions) }}"
                    @endif
                    @if(count($selectedCatalogItems))
                        :selected-product-types="{{ json_encode($selectedCatalogItems) }}"
                    @endif

                    @if(count($selectedBestSalesProducts))
                        :selected-best-sales-products="{{ json_encode($selectedBestSalesProducts) }}"
                    @endif

                    @if(count($brands))
                        :selected-brands="{{ json_encode($brands) }}"
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
