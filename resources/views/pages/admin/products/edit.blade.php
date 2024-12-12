@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">

                @if(Session::has('success'))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-success" role="alert">
                                {{ Session::get('success') }}
                            </div>
                        </div>
                    </div>
                @endif
                @if(Session::has('error'))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-danger" role="alert">
                                {{ Session::get('error') }}
                            </div>
                        </div>
                    </div>
                @endif

                @if(isset($product))
                    <h2 class="page-title">{{ trans('admin.product_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.product_new') }}</h2>
                @endisset

                    {{-- TODO: I have to clean this --}}
                    @php
                        // availability status
                        $availability_status_options = [];
                        $availability_status_option_selected = 1;
                        foreach ($productStatuses as $status) {
                            if(isset($product) && $product->availability_status_id === $status['id']) {
                                $availability_status_option_selected = $status['id'];
                            }
                            $availability_status_options[$status['id']] = $status['name'];
                        }

                        // currencies
                        $currency_options = [];
                        $currency_selected = 1; // currency must be UAH by default
                        foreach ($currencies as $currency) {
                            /*if(isset($product) && $product->price_currency_id === $currency->id) {
                                $currency_selected = $currency->id;
                            }*/
                            $currency_options[$currency->id] = $currency->name;
                        }

                        // category
                        if( $productType->has_category ) {
                            $category_options = [];
                            $category_selected = (isset($categories[0])) ? $categories[0]['id']: 1;

                            foreach ($categories as $category) {
                                if(isset($product) && $product->categories->contains('id', $category->id)) {
                                    $category_selected = $category->id;
                                }
                                $category_options[$category->id] = $category->name;
                            }
                        }


                        // brands
                        $brand_options = [];
                        $brand_selected = 1;
                        if(isset($product) && !is_null($product->brand)) {
                            $brand_selected = $product->brand->id;
                        }
                        if( $productType->has_brand ) {
                            foreach ($brands as $brand) {
                                $brand_options[$brand->id] = $brand->name;
                            }
                        }



                        // custom options
                        foreach ($productType->fields as $key => $field) {

                            // custom fields with images return array,
                            // custom fields with NO images return number as string,
                            if( isset($product) ) {
                                $selectedFieldValue = $product->getCustomFieldValue($field->id);
                                $selectedFieldValue = ( is_array($selectedFieldValue) ) ? $selectedFieldValue[0] : $selectedFieldValue;
                            }

                            $optionsArray = [];
                            foreach ($field->options as $optionSingle) {
                                $optionsArray[$optionSingle->id] = $optionSingle->name;

                                if(isset($product) && $selectedFieldValue == $optionSingle->id) {
                                    $productType->fields[$key]->custom_options_selected = $optionSingle->id;
                                }
                            }
                            $productType->fields[$key]->custom_options = $optionsArray;
                        }
                    @endphp

                    <product-page-edit-form
                        base-language="{{ $baseLanguage }}"
                        :available-languages="{{ json_encode($availableLanguages) }}"
                        sub-product-search-route="{{ route('admin.product.list.sub') }}"
                        submit-route="{{ isset($product) ?  route('admin.product.edit', ['productType' => $productType['id'], 'product' => $product->id]) : route('admin.product.create', ['productType' => $productType->id]) }}"

                        @if(isset($product))
                            :product-name="{{ json_encode($product->getTranslations('name')) }}"
                            :product-sku="{{ json_encode($product['sku']) }}"
                            :product-slug="{{ json_encode($product['slug']) }}"
                            :product-meta-title="{{ json_encode($product->getTranslations('meta_title')) }}"
                            :product-meta-description="{{ json_encode($product->getTranslations('meta_description')) }}"
                            :product-meta-keywords="{{ json_encode($product->getTranslations('meta_keywords')) }}"
                            :product-meta-tags="{{ json_encode($product['meta_tags']) }}"
                            :product-created-at="{{ json_encode($product->created_at->format('Y-m-d H:i:s')) }}"
                        @endif


                        @if(isset($subProducts) && count($subProducts))
                            :selected-sub-products="{{ json_encode($subProducts) }}"
                        @endif

                        :availability-status-options="{{ json_encode( $availability_status_options ) }}"
                        :availability-status-options-selected="{{ $availability_status_option_selected }}"

                        @if(isset($product))
                            :old-price="{{ number_format($product['old_price'], 2, '.', '') }}"
                            :price="{{ json_encode($product['price']) }}"
                            :purchase-price-in-currency="{{ json_encode($product['purchase_price_in_currency']) }}"
                        @endif

                        :currency-options="{{ json_encode( $currency_options ) }}"
                        :currency-selected="{{ json_encode($currency_selected) }}"

                        @if($productType->has_category)
                            :category-display="{{ $productType->has_category }}"
                            :category-options="{{ json_encode( $category_options ) }}"
                            :category-selected="{{ $category_selected }}"
                        @endif

                        @if($productType->has_brand)
                            :brand-display="{{ $productType->has_brand }}"
                            :brand-options="{{ json_encode( $brand_options ) }}"
                            :brand-selected="{{ json_encode($brand_selected) }}"
                        @endif

                        :color-options="{{ json_encode( $colors ) }}"
                        :color-display="{{ $productType->has_color }}"
                        @if(isset($product) && $productType->has_color)
                            :color-selected="{{ json_encode($product->colors) }}"
                            :main-color-selected="{{ json_encode($product->main_color_id) }}"
                        @endif

                        @if(isset($productShortText['content']))
                            :product-short-text="{{ json_encode($productShortText['content']) }}"
                        @endif

                        @if(isset($productText['content']))
                            :product-text="{{ json_encode($productText['content']) }}"
                        @endif

                        @if(isset($product))
                            :product-main-image="{{ json_encode($product->main_image_url) }}"
                        @endif

                        @if(isset($productGallery) && count($productGallery))
                            :product-gallery="{{ json_encode($productGallery) }}"
                        @endif

                        @if(isset($characteristics) && count($characteristics))
                            :product-characteristics="{{ json_encode($characteristics) }}"
                        @endif

                        @if(isset($productVideos) && count($productVideos))
                            :product-videos="{{ json_encode($productVideos) }}"
                        @endif

                        :product-custom-fields="{{ json_encode($productType->fields) }}"

                        :product-custom-attributes="{{ json_encode($productType->attributes) }}"
                        @if(isset($attributeOptions) && count($attributeOptions))
                            :product-attribute-options="{{ json_encode($attributeOptions) }}"
                        @endif

                        @if(isset($productFaqs) && count($productFaqs))
                            :product-faqs="{{ json_encode($productFaqs) }}"
                        @endif

                        @if(isset($seoData['title']))
                            :seo-title="{{ json_encode($seoData['title']) }}"
                        @endif
                        @if(isset($seoData['content']))
                            :seo-text="{{ json_encode($seoData['content']) }}"
                        @endif
                        {{--end--}}
                    />

            </div>
        </div>
    </div>
@endsection

@section('vue')
    <vue/>
@endsection

