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

{{--                @dd(json_encode($productStatuses))--}}
{{--                @dd(json_encode($product['price_in_currency']))--}}
{{--                @dd( number_format($product['old_price_in_currency'], 2, '.', '') )--}}

{{--                    @dd( json_encode($productType->fields) )--}}

                    @php
                        //print_r( json_encode($product->getTranslations('name') ));

                        $arr = [
                            "uk"=> "name UK",
                            "ru"=>"name RU"
                                ];

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
                        $category_options = [];
                        $category_selected = 1;
                        if( $productType->has_category ) {

                            foreach ($categories as $category) {
                                if(isset($product) && $product->categories->contains('id', $category->id)) {
                                    $category_selected = $category->id;
                                }
                                $category_options[$category->id] = $category->name;
                            }

                        }

                        // custom options
                        foreach ($productType->fields as $key => $field) {
                            $optionsArray = [];
                            foreach ($field->options as $optionSingle) {
                                $optionsArray[$optionSingle->id] = $optionSingle->name;

                                if(isset($product) && $product->getCustomFieldValue($field->id) == $optionSingle->id) {
                                    $productType->fields[$key]->custom_options_selected = $optionSingle->id;
                                }
                            }
                            $productType->fields[$key]->custom_options = $optionsArray;
                        }
                    @endphp


{{--                    @dd($colors)--}}


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
                        @endif


                        @if(isset($subProducts) && count($subProducts))
                            :selected-sub-products="{{ json_encode($subProducts) }}"
                        @endif

                        :availability-status-options="{{ json_encode( $availability_status_options ) }}"
                        :availability-status-options-selected="{{ json_encode($availability_status_option_selected) }}"

                        @if(isset($product))
                            :old-price-in-currency="{{ number_format($product['old_price_in_currency'], 2, '.', '') }}"
                            :price-in-currency="{{ json_encode($product['price_in_currency']) }}"
                            :purchase-price-in-currency="{{ json_encode($product['purchase_price_in_currency']) }}"
                        @endif

                        :currency-options="{{ json_encode( $currency_options ) }}"
                        :currency-selected="{{ json_encode($currency_selected) }}"

                        @if($productType->has_category)
                            :category-options="{{ json_encode( $category_options ) }}"
                            :category-selected="{{ json_encode($category_selected) }}"
                        @endif

                        @if($productType->has_color)
                            :color-options="{{ json_encode( $colors ) }}"
{{--                            :category-selected="{{ json_encode($category_selected) }}"--}}
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
                        {{--end--}}
                    />

            </div>
        </div>
    </div>
@endsection

@section('vue')
    <vue/>
@endsection

