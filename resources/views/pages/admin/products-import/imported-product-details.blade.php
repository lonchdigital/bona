@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="mb-2 page-title">{{ trans('admin.products_import_details') }}</h2>
                <p class="card-text">{{ trans('admin.products_import_details_description') }}</p>
                <div class="row my-4">
                    <!-- Small table -->
                    <div class="col-md-12">
                        <div class="card shadow">
                            <div class="card-body">
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.parent_product') }}</striong>
                                    <div class="mt-1">
                                        @if($importedProduct->parent)
                                            <a href="{{ route('admin.products-import.details', ['importedProduct' => $importedProduct->parent->id]) }}">{{ $importedProduct->parent->name }}</a>
                                        @else
                                            {{ trans('admin.no_parent_product') }}
                                        @endif
                                    </div>
                                </div>
                                @foreach($availableLanguages as $availableLanguage)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.name') }} {{ mb_strtoupper($availableLanguage) }}</striong>
                                        <div class="mt-1">{{ $importedProduct->getTranslations('name')[$availableLanguage] ?? '' }}</div>
                                    </div>
                                @endforeach
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.sku') }}</striong>
                                    <div class="mt-1">{{ $importedProduct->sku }}</div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.slug') }}</striong>
                                    <div class="mt-1">{{ $importedProduct->slug }}</div>
                                </div>
                                @foreach($availableLanguages as $availableLanguage)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.meta_title') }} {{ mb_strtoupper($availableLanguage) }}</striong>
                                        <div class="mt-1">{{ $importedProduct->getTranslations('meta_title')[$availableLanguage] ?? '' }}</div>
                                    </div>
                                @endforeach
                                @foreach($availableLanguages as $availableLanguage)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.meta_description') }} {{ mb_strtoupper($availableLanguage) }}</striong>
                                        <div class="mt-1">{{ $importedProduct->getTranslations('meta_description')[$availableLanguage] ?? '' }}</div>
                                    </div>
                                @endforeach
                                @foreach($availableLanguages as $availableLanguage)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.meta_keywords') }} {{ mb_strtoupper($availableLanguage) }}</striong>
                                        <div class="mt-1">{{ $importedProduct->getTranslations('meta_keywords')[$availableLanguage] ?? '' }}</div>
                                    </div>
                                @endforeach
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.special_offer') }}</striong>
                                    <div class="mt-1">
                                        {{ \App\DataClasses\ProductSpecialOfferOptionsDataClass::get()->whereIn('id', $importedProduct->special_offers)->pluck('name')->implode(', ') }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.old_price_in_currency') }}</striong>
                                    <div class="mt-1">
                                        {{ $importedProduct->old_price_in_currency }} {{ $importedProduct->currency->code }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.price_in_currency') }}</striong>
                                    <div class="mt-1">
                                        {{ $importedProduct->price_in_currency }} {{ $importedProduct->currency->code }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.purchase_price_in_currency') }}</striong>
                                    <div class="mt-1">
                                        {{ $importedProduct->purchase_price_in_currency }} {{ $importedProduct->currency->code }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.currency_code') }}</striong>
                                    <div class="mt-1">
                                        {{ $importedProduct->currency->code }}
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.product_main_image') }}</striong>
                                    <div class="mt-1">
                                        @if ($importedProduct->main_image_url)
                                            <img src="{{ $importedProduct->main_image_url }}" alt="{{ trans('admin.product_main_image') }}" class="category-img rounded mb-3">
                                        @else
                                            {{ trans('admin.no_image') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.product_image_pattern') }}</striong>
                                    <div class="mt-1">
                                        @if ($importedProduct->pattern_image_url)
                                            <img src="{{ $importedProduct->pattern_image_url }}" alt="{{ trans('admin.product_image_pattern') }}" class="category-img rounded mb-3">
                                        @else
                                            {{ trans('admin.no_image') }}
                                        @endif
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col d-flex flex-wrap justify-content-between">
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.product_gallery_image_1') }}</striong>
                                            <div class="mt-1">
                                                @if ($importedProduct->gallery_image_1_url)
                                                    <img src="{{ $importedProduct->gallery_image_1_url }}" alt="{{ trans('admin.product_gallery_image_1') }}" class="category-img rounded mb-3">
                                                @else
                                                    {{ trans('admin.no_image') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.product_gallery_image_2') }}</striong>
                                            <div class="mt-1">
                                                @if ($importedProduct->gallery_image_2_url)
                                                    <img src="{{ $importedProduct->gallery_image_2url }}" alt="{{ trans('admin.product_gallery_image_2') }}" class="category-img rounded mb-3">
                                                @else
                                                    {{ trans('admin.no_image') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.product_gallery_image_3') }}</striong>
                                            <div class="mt-1">
                                                @if ($importedProduct->gallery_image_3_url)
                                                    <img src="{{ $importedProduct->gallery_image_3_url }}" alt="{{ trans('admin.product_gallery_image_3') }}" class="category-img rounded mb-3">
                                                @else
                                                    {{ trans('admin.no_image') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.product_gallery_image_4') }}</striong>
                                            <div class="mt-1">
                                                @if ($importedProduct->gallery_image_4_url)
                                                    <img src="{{ $importedProduct->gallery_image_4_url }}" alt="{{ trans('admin.product_gallery_image_4') }}" class="category-img rounded mb-3">
                                                @else
                                                    {{ trans('admin.no_image') }}
                                                @endif
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.product_gallery_image_5') }}</striong>
                                            <div class="mt-1">
                                                @if ($importedProduct->gallery_image_5_url)
                                                    <img src="{{ $importedProduct->gallery_image_1_url }}" alt="{{ trans('admin.product_gallery_image_5') }}" class="category-img rounded mb-3">
                                                @else
                                                    {{ trans('admin.no_image') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <striong class="text-dark">{{ trans('admin.country') }}</striong>
                                    <div class="mt-1">
                                        {{ $importedProduct->country->name }}
                                    </div>
                                </div>
                                @if ($importedProduct->productType->has_brand)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.brand') }}</striong>
                                        <div class="mt-1">
                                            {{ $importedProduct->brand->name }}
                                        </div>
                                    </div>
                                @endif
                                @if ($importedProduct->productType->has_collection)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.collection') }}</striong>
                                        <div class="mt-1">
                                            {{ $importedProduct->collection->name }}
                                        </div>
                                    </div>
                                @endif
                                @if ($importedProduct->productType->has_category)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.category') }}</striong>
                                        <div class="mt-1">
                                            {{ $importedProduct->categories->pluck('name')->implode(', ') }}
                                        </div>
                                    </div>
                                @endif
                                @if ($importedProduct->productType->has_color)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.color') }}</striong>
                                        <div class="mt-1">
                                            {{ $importedProduct->color->name }}
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ trans('admin.all_colors') }}</striong>
                                        <div class="mt-1">
                                            {{ $importedProduct->colors->pluck('name')->implode(', ') }}
                                        </div>
                                    </div>
                                @endif
                                @if ($importedProduct->productType->has_size)
                                    @if ($importedProduct->productType->has_length)
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.length') }}</striong>
                                            <div class="mt-1">
                                                {{ $importedProduct->length }}
                                            </div>
                                        </div>
                                    @endif
                                    @if ($importedProduct->productType->has_width)
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.width') }}</striong>
                                            <div class="mt-1">
                                                {{ $importedProduct->width }}
                                            </div>
                                        </div>
                                    @endif
                                    @if ($importedProduct->productType->has_height)
                                        <div class="mb-3">
                                            <striong class="text-dark">{{ trans('admin.height') }}</striong>
                                            <div class="mt-1">
                                                {{ $importedProduct->height }}
                                            </div>
                                        </div>
                                    @endif
                                @endif
                                @foreach($importedProduct->productType->fields as $customField)
                                    <div class="mb-3">
                                        <striong class="text-dark">{{ $customField->field_name }}</striong>
                                        <div class="mt-1">
                                            @if ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING)
                                                {{ $importedProduct->getCustomFieldValue($customField->id) }}
                                            @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER)
                                                {{ $importedProduct->getCustomFieldValue($customField->id) }}
                                            @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE)
                                                {{ $importedProduct->getCustomFieldValue($customField->id) }} {{ $customField->field_size_name }}
                                            @elseif($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION)
                                                @if ($customField->is_multiselectable)
                                                    {{ $customField->options()->where('product_field_id', $customField->id)->whereIn('id', $importedProduct->getCustomFieldValue($customField->id))->get()->pluck('name')->implode(', ') }}
                                                @else
                                                    {{ $customField->options()->where('product_field_id', $customField->id)->where('id', $importedProduct->getCustomFieldValue($customField->id))->first()->name }}
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('scripts')

@endpush
