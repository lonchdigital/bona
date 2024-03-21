@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($productType)
                    <h2 class="page-title">{{ trans('admin.product_type_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.product_type_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.product_type_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST"
                                               action="{{ isset($productType) ? route('admin.product-type.edit', ['productType' => $productType['id']])  : route('admin.product-type.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">

                                    <p>
                                        <strong>
                                            {{ trans('admin.product_type_base_info') }}
                                        </strong>
                                    </p>


                                    <!-- PRODUCT NAME -->
                                    <x-admin.multilanguage-input :placeholder="trans('admin.product_type_name_example')"
                                                                 :label="trans('admin.name')" :is-required="true"
                                                                 field-name="name"
                                                                 :values="isset($productType) ? $productType->getTranslations('name') : []"/>

                                    <!-- SLUG -->
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control"
                                               @isset($productType) value="{{ $productType['slug'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                                    </div>

                                    <!-- PRODUCT POINT NAME -->
                                    <x-admin.multilanguage-input
                                        :placeholder="trans('admin.product_point_name_example')"
                                        :label="trans('admin.product_point_name')" field-name="product_point_name"
                                        :values="isset($productType) ? $productType->getTranslations('product_point_name') : []"/>


                                    <div class="form-group">
                                        <label for="product_type_image">{{ trans('admin.product_type_image') }} <strong class="text-danger">*</strong> (jpeg,png,jpg)</label>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <img @if(isset($productType) && isset($productType->image_url)) src="{{ $productType->image_url }}" @else style="display: none;" @endif id="product_type_image" alt="{{ trans('admin.product_category_image') }}" class="category-img rounded mb-3">
                                            </div>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="product_type_image" id="product_type_image_input">
                                            <label class="custom-file-label" for="product_type_image">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-product_type_image"></div>
                                        </div>
                                    </div>


                                    <p class="mt-5">
                                        <strong>
                                            {{ trans('admin.product_type_seo_config') }}
                                        </strong>
                                    </p>

                                    <!-- PRODUCT TYPE META TITLE -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_title')" :is-required="false"
                                                                 field-name="meta_title"
                                                                 :values="isset($productType) ? $productType->getTranslations('meta_title') : []"/>
                                    <!-- PRODUCT TYPE META DESCRIPTION -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_description')"
                                                                 :is-required="false" field-name="meta_description"
                                                                 :values="isset($productType) ? $productType->getTranslations('meta_description') : []"/>
                                    <!-- PRODUCT TYPE META KEY WORDS -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_keywords')"
                                                                 :is-required="false" field-name="meta_keywords"
                                                                 :values="isset($productType) ? $productType->getTranslations('meta_keywords') : []"/>

                                    <p class="mt-5">
                                        <strong>
                                            {{ trans('admin.product_type_base_params') }}
                                        </strong>
                                    </p>

                                    <!-- PRODUCT TYPE HAS BRAND -->
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="hidden" name="product_has_brand" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox"
                                               id="product_has_brand" name="product_has_brand"
                                               @if(isset($productType) && $productType->has_brand) checked @endif>
                                        <label class="custom-control-label"
                                               for="product_has_brand">{{ trans('admin.has_brand') }}</label>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-product_has_brand"></div>
                                    </div>

                                    <!-- PRODUCT TYPE HAS COLOR -->
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="hidden" name="product_has_color" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox"
                                               id="product_has_color" name="product_has_color"
                                               @if(isset($productType) && $productType->has_color) checked @endif>
                                        <label class="custom-control-label"
                                               for="product_has_color">{{ trans('admin.has_color') }}</label>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-product_has_color"></div>
                                    </div>

                                    <!-- PRODUCT TYPE HAS COLLECTION -->
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="hidden" name="product_has_collection" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox"
                                               id="product_has_collection" name="product_has_collection"
                                               @if(isset($productType) && $productType->has_collection) checked @endif>
                                        <label class="custom-control-label"
                                               for="product_has_collection">{{ trans('admin.has_collection') }}</label>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-product_has_collection"></div>
                                    </div>

                                    <!-- PRODUCT TYPE HAS CATEGORY -->
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="hidden" name="product_has_category" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox"
                                               id="product_has_category" name="product_has_category"
                                               @if(isset($productType) && $productType->has_category) checked @endif>
                                        <label class="custom-control-label"
                                               for="product_has_category">{{ trans('admin.has_category') }}</label>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-product_has_category"></div>
                                    </div>

                                    <!-- PRODUCT TYPE HAS SIZE -->
                                    <div class="custom-control custom-checkbox mb-3">
                                        <input type="hidden" name="product_has_size" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox"
                                               id="product_has_size" name="product_has_size"
                                               @if(isset($productType) && $productType->has_size) checked @endif>
                                        <label class="custom-control-label"
                                               for="product_has_size">{{ trans('admin.has_size') }}</label>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-product_has_size"></div>
                                    </div>

                                    <div class="row">
                                        <div
                                            class="col @if(!(isset($productType) && $productType->has_size)) d-none @endif"
                                            id="product-has-size-block">

                                            <p class="mt-5">
                                                <strong>
                                                    {{ trans('admin.product_type_size_params') }}
                                                </strong>
                                            </p>

                                            <!-- PRODUCT TYPE HAS LENGTH -->
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_has_length" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox"
                                                       id="product_has_length" name="product_has_length"
                                                       @if(isset($productType) && $productType->has_length) checked @endif>
                                                <label class="custom-control-label"
                                                       for="product_has_length">{{ trans('admin.has_length') }}</label>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-product_has_length"></div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY LENGTH -->
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_filter_by_length" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox"
                                                       id="product_filter_by_length" name="product_filter_by_length"
                                                       @if(isset($productType) && $productType->filter_by_length) checked @endif>
                                                <label class="custom-control-label"
                                                       for="product_filter_by_length">{{ trans('admin.filter_by_length') }}</label>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-filter_by_length"></div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY LENGTH CONFIG -->
                                            <div class="row">
                                                <div
                                                    class="col @if(!(isset($productType) && $productType->filter_by_length)) d-none @endif"
                                                    id="length-filter-block">
                                                    <x-admin.size-filter-block
                                                        :type="\App\DataClasses\ProductSizeTypesDataClass::LENGTH"
                                                        :product-type="$productType ?? null"/>
                                                </div>
                                            </div>

                                            <!-- PRODUCT TYPE HAS WIDTH -->
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_has_width" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox"
                                                       id="product_has_width" name="product_has_width"
                                                       @if(isset($productType) && $productType->has_width) checked @endif>
                                                <label class="custom-control-label"
                                                       for="product_has_width">{{ trans('admin.has_width') }}</label>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-product_has_width"></div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY WIDTH -->
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_filter_by_width" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox"
                                                       id="product_filter_by_width" name="product_filter_by_width"
                                                       @if(isset($productType) && $productType->filter_by_width) checked @endif>
                                                <label class="custom-control-label"
                                                       for="product_filter_by_width">{{ trans('admin.filter_by_width') }}</label>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-filter_by_width"></div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY WIDTH CONFIG -->
                                            <div class="row">
                                                <div
                                                    class="col @if(!(isset($productType) && $productType->filter_by_width)) d-none @endif"
                                                    id="width-filter-block">
                                                    <x-admin.size-filter-block
                                                        :type="\App\DataClasses\ProductSizeTypesDataClass::WIDTH"
                                                        :product-type="$productType ?? null"/>
                                                </div>
                                            </div>

                                            <!-- PRODUCT TYPE HAS HEIGHT -->
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_has_height" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox"
                                                       id="product_has_height" name="product_has_height"
                                                       @if(isset($productType) && $productType->has_height) checked @endif>
                                                <label class="custom-control-label"
                                                       for="product_has_height">{{ trans('admin.has_height') }}</label>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-product_has_height"></div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY HEIGHT -->
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_filter_by_height" value="0">
                                                <input class="custom-control-input" value="1" type="checkbox"
                                                       id="product_filter_by_height" name="product_filter_by_height"
                                                       @if(isset($productType) && $productType->filter_by_height) checked @endif>
                                                <label class="custom-control-label"
                                                       for="product_filter_by_height">{{ trans('admin.filter_by_height') }}</label>
                                                <div class="mt-1 text-danger ajaxError"
                                                     id="error-field-filter_by_height"></div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY HEIGHT CONFIG -->
                                            <div class="row">
                                                <div
                                                    class="col @if(!(isset($productType) && $productType->filter_by_height)) d-none @endif"
                                                    id="height-filter-block">
                                                    <x-admin.size-filter-block
                                                        :type="\App\DataClasses\ProductSizeTypesDataClass::HEIGHT"
                                                        :product-type="$productType ?? null"/>
                                                </div>
                                            </div>

                                            <!-- PRODUCT TYPE FILTER BY SIZE POINTS NAME -->
                                            <x-admin.multilanguage-input :is-required="true"
                                                                         :label="trans('admin.size_points')"
                                                                         field-name="size_points"
                                                                         :values="isset($productType) ? $productType->getTranslations('size_points') : []"/>
                                        </div>
                                    </div>

                                    <p class="mt-5">
                                        <strong>
                                            {{ trans('admin.product_type_custom_fields') }}
                                        </strong>
                                    </p>


                                    <div class="row">
                                        <div class="col-md-12" id="fields">
                                            @if(isset($productType) && $productType->fields)
                                                @foreach($productType->fields as $field)
                                                    <div class="row field pb-1" id="field-id-{{ $field->id }}">
                                                        <div class="col-md-12">
                                                            <div class="border border-secondary rounded p-3">
                                                                <div
                                                                    class="row justify-content-between align-items-center">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                for="field-select-{{ $field->id }}">{{ trans('admin.product_field') }}
                                                                                <strong
                                                                                    class="text-danger">*</strong></label>
                                                                            <select class="form-control select2"
                                                                                    id="field-select-field-{{ $field->id }}"
                                                                                    name="product_field[{{ $field->id }}][id]">
                                                                                <option value=""
                                                                                        @if(!isset($productType)) selected
                                                                                        @endif disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field'))  }}</option>
                                                                                @foreach($productFields as $productField)
                                                                                    <option
                                                                                        value="{{ $productField->id }}"
                                                                                        @if($field->id == $productField->id) selected @endif>{{ $productField->field_name }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                        <div
                                                                            class="custom-control custom-checkbox mb-3">
                                                                            <input type="hidden"
                                                                                   name="product_field[{{ $field->id }}][show_as_filter]"
                                                                                   value="0">
                                                                            <input class="custom-control-input"
                                                                                   value="1" type="checkbox"
                                                                                   id="field-as-filter-{{ $field->id }}"
                                                                                   name="product_field[{{ $field->id }}][show_as_filter]"
                                                                                   @if($field->pivot->show_as_filter) checked @endif>
                                                                            <label class="custom-control-label"
                                                                                   for="field-as-filter-{{ $field->id }}">{{ trans('admin.show_as_filter') }}</label>
                                                                        </div>
                                                                        <div class="row"
                                                                             id="field-filter-name-{{ $field->id }}"
                                                                             @if(!$field->pivot->show_as_filter) style="display: none;" @endif>
                                                                            <div class="col-md-12">
                                                                                <div
                                                                                    class="custom-control custom-checkbox mb-3">
                                                                                    <input type="hidden"
                                                                                           name="product_field[{{ $field->id }}][show_on_main_filters_list]"
                                                                                           value="0">
                                                                                    <input class="custom-control-input"
                                                                                           value="1" type="checkbox"
                                                                                           id="show-field-on-main-filter-list-{{ $field->id }}"
                                                                                           name="product_field[{{ $field->id }}][show_on_main_filters_list]"
                                                                                           @if($field->pivot->show_on_main_filters_list) checked @endif>
                                                                                    <label class="custom-control-label"
                                                                                           for="show-field-on-main-filter-list-{{ $field->id }}">{{ trans('admin.filter_show_on_main_filters_list') }}</label>
                                                                                </div>
                                                                                <x-admin.multilanguage-input
                                                                                    :is-required="true"
                                                                                    :label="trans('admin.filter_name')"
                                                                                    field-name="product_field[{{ $field->id }}][filter_name]"
                                                                                    :values="$field->pivot->filter_name ? $field->pivot->getTranslations('filter_name') : []"/>
                                                                                <div class="form-group mb-3">
                                                                                    <label
                                                                                        for="field-select-{{ $field->id }}">{{ trans('admin.filter_full_position') }}
                                                                                        <strong
                                                                                            class="text-danger">*</strong></label>
                                                                                    <select class="form-control select2"
                                                                                            id="field-select-position-{{ $field->id }}"
                                                                                            name="product_field[{{ $field->id }}][filter_full_position_id]">
                                                                                        @foreach(\App\DataClasses\ProductFilterFullPositionOptionsDataClass::get() as $filterFullPosition)
                                                                                            <option
                                                                                                value="{{ $filterFullPosition['id'] }}"
                                                                                                @if($field->pivot->filter_full_position_id == $filterFullPosition['id']) selected @endif>{{ $filterFullPosition['name'] }}</option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="mt-1 text-danger ajaxError"
                                                                                     id="error-field-product_has_category"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <a href="#" class="btn btn-danger"
                                                                           onclick="removeField(event, {{ $field->id }})">
                                                                            <span
                                                                                class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                            {{ trans('admin.field_delete') }}
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="mt-1 text-danger ajaxError"
                                                                 id="error-field-product_field.0.id"></div>
                                                        </div>
                                                    </div>

                                                @endforeach
                                            @else
                                                <div class="row field pb-1" id="field-id-0">
                                                    <div class="col-md-12">
                                                        <div class="border border-secondary rounded p-3">
                                                            <div class="row justify-content-between align-items-center">
                                                                <div class="col-md-12">
                                                                    <div class="form-group mb-3">
                                                                        <label
                                                                            for="field-select-0">{{ trans('admin.product_field') }}
                                                                            <strong
                                                                                class="text-danger">*</strong></label>
                                                                        <select class="form-control select2"
                                                                                id="field-select-0"
                                                                                name="product_field[0][id]">
                                                                            <option value="" selected
                                                                                    disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field'))  }}</option>
                                                                            @foreach($productFields as $productField)
                                                                                <option
                                                                                    value="{{ $productField->id }}">{{ $productField->field_name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                    <div class="custom-control custom-checkbox mb-3">
                                                                        <input type="hidden"
                                                                               name="product_field[0][show_as_filter]"
                                                                               value="0">
                                                                        <input class="custom-control-input" value="1"
                                                                               type="checkbox" id="field-as-filter-0"
                                                                               name="product_field[0][show_as_filter]">
                                                                        <label class="custom-control-label"
                                                                               for="field-as-filter-field-0">{{ trans('admin.show_as_filter') }}</label>
                                                                        <div class="mt-1 text-danger ajaxError"
                                                                             id="error-field-product_has_category"></div>
                                                                    </div>
                                                                    <div class="row" id="field-filter-name-0"
                                                                         style="display: none;">
                                                                        <div class="col-md-12">
                                                                            <div
                                                                                class="custom-control custom-checkbox mb-3">
                                                                                <input type="hidden"
                                                                                       name="product_field[0][show_on_main_filters_list]"
                                                                                       value="0">
                                                                                <input class="custom-control-input"
                                                                                       value="1" type="checkbox"
                                                                                       id="show-field-on-main-filter-list-0"
                                                                                       name="product_field[0][show_on_main_filters_list]">
                                                                                <label class="custom-control-label"
                                                                                       for="show-field-on-main-filter-list-0">{{ trans('admin.filter_show_on_main_filters_list') }}</label>
                                                                            </div>
                                                                            <x-admin.multilanguage-input
                                                                                :is-required="true"
                                                                                :label="trans('admin.filter_name')"
                                                                                field-name="product_field[0][filter_name]"
                                                                                :values="[]"/>
                                                                            <div class="form-group mb-3">
                                                                                <label
                                                                                    for="field-select-0">{{ trans('admin.filter_full_position') }}
                                                                                    <strong
                                                                                        class="text-danger">*</strong></label>
                                                                                <select class="form-control select2"
                                                                                        id="field-select-position-0"
                                                                                        name="product_field[0][filter_full_position_id]">
                                                                                    @foreach(\App\DataClasses\ProductFilterFullPositionOptionsDataClass::get() as $filterFullPosition)
                                                                                        <option
                                                                                            value="{{ $filterFullPosition['id'] }}">{{ $filterFullPosition['name'] }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>
                                                                            <div class="mt-1 text-danger ajaxError"
                                                                                 id="error-field-product_has_category"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <a href="#" class="btn btn-danger"
                                                                       onclick="removeField(event, 0)">
                                                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                        {{ trans('admin.field_delete') }}
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mt-1 text-danger ajaxError"
                                                             id="error-field-product_field.0.id"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-12 text-center">
                                            <a href="#" id="add-field" class="btn mb-2 btn-secondary">
                                                <span class="fe fe-plus-square fe-16 mr-2"></span>
                                                {{ trans('admin.field_add') }}
                                            </a>
                                        </div>
                                    </div>



                                    <p class="mt-5">
                                        <strong>
                                            {{ 'Attributes' }}
                                        </strong>
                                    </p>

                                    <div class="row">
                                        <div class="col-md-12" id="attributes">
                                            @if(isset($productType) && $productType->attributes)
                                                {{-- always runs here --}}
                                                @foreach($productType->attributes as $attribute)
                                                    <div class="row field pb-1" id="attribute-id-{{ $attribute->id }}">
                                                        <div class="col-md-12">
                                                            <div class="border border-secondary rounded p-3">
                                                                <div
                                                                    class="row justify-content-between align-items-center">
                                                                    <div class="col-md-12">
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                for="attribute-select-{{ $attribute->id }}">{{ trans('admin.product_field') }}
                                                                                <strong
                                                                                    class="text-danger">*</strong></label>
                                                                            <select class="form-control select2"
                                                                                    id="attribute-select-attribute-{{ $attribute->id }}"
                                                                                    name="product_attribute[{{ $attribute->id }}][id]">
                                                                                <option value=""
                                                                                        @if(!isset($productType)) selected @endif disabled>
                                                                                    {{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field'))  }}
                                                                                </option>
                                                                                @foreach($productAttributes as $productAttribute)
                                                                                    <option
                                                                                        value="{{ $productAttribute->id }}"
                                                                                        @if($attribute->id == $productAttribute->id) selected @endif>{{ $productAttribute->attribute_name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-2">
                                                                        <a href="#" class="btn btn-danger"
                                                                           onclick="artRemoveAttribute(event, {{ $attribute->id }})">
                                                                            <span
                                                                                class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                            {{ trans('admin.field_delete') }}
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                            <div class="mt-1 text-danger ajaxError"
                                                                 id="error-field-product_field.0.id"></div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="row attribute pb-1" id="attribute-id-0">
                                                    <div class="col-md-12">
                                                        <div class="border border-secondary rounded p-3">
                                                            <div class="row justify-content-between align-items-center">
                                                                <div class="col-md-12">
                                                                    <div class="form-group mb-3">
                                                                        <label
                                                                            for="attribute-select-0">{{ trans('admin.product_field') }}
                                                                            <strong
                                                                                class="text-danger">*</strong></label>
                                                                        <select class="form-control select2"
                                                                                id="attribute-select-0"
                                                                                name="product_attribute[0][id]">
                                                                            <option value="" selected
                                                                                    disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field'))  }}</option>
                                                                            @foreach($productAttributes as $productAttribute)
                                                                                <option
                                                                                    value="{{ $productAttribute->id }}">{{ $productAttribute->attribute_name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>

                                                                </div>
                                                                <div class="col-md-2">
                                                                    <a href="#" class="btn btn-danger"
                                                                       onclick="artRemoveAttribute(event, 0)">
                                                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                        {{ trans('admin.field_delete') }}
                                                                    </a>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="mt-1 text-danger ajaxError"
                                                             id="error-field-product_attribute.0.id"></div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-12 text-center">
                                            <a href="#" id="add-attribute" class="btn mb-2 btn-secondary">
                                                <span class="fe fe-plus-square fe-16 mr-2"></span>
                                                {{ trans('admin.attribute_add') }}
                                            </a>
                                        </div>
                                    </div>



                                    <p class="mt-5">
                                        <strong>
                                            {{ trans('admin.questions') }}
                                        </strong>
                                    </p>

                                    <div class="row">
                                        <div class="col-md-12" id="faqs">

                                            @if(isset($productType) && $faqsData)
                                                @foreach($faqsData as $faqItem)
                                                    <div class="row faq-row pb-1" id="faq-id-{{ $faqItem->id }}">
                                                        <div class="col-md-12">
                                                            <div class="border border-secondary rounded p-3">
                                                                <div class="row justify-content-between align-items-center">

                                                                    <div class="col-md-12">
                                                                        <div class="row" id="faq-name-{{ $faqItem->id }}">
                                                                            <div class="col-md-12">
                                                                                <x-admin.multilanguage-input
                                                                                    :is-required="true"
                                                                                    :label="trans('admin.faq_question')"
                                                                                    field-name="faqs[{{ $faqItem->id }}][question]"
                                                                                    :values="$faqItem->question ? $faqItem->getTranslations('question') : []"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-12">
                                                                        <div class="row" id="faq-name-{{ $faqItem->id }}">
                                                                            <div class="col-md-12">
                                                                                <x-admin.multilanguage-text-area
                                                                                    :is-required="true"
                                                                                    :label="trans('admin.faq_answer')"
                                                                                    field-name="faqs[{{ $faqItem->id }}][answer]"
                                                                                    :values="$faqItem->answer ? $faqItem->getTranslations('answer') : []"/>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="col-md-2">
                                                                        <a href="#" class="btn btn-danger"
                                                                           onclick="artRemoveQuestion(event, {{ $faqItem->id }})">
                                                                            <span
                                                                                class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                            {{ trans('admin.delete_question') }}
                                                                        </a>
                                                                    </div>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-12 text-center">
                                            <a href="#" id="add-question" class="btn mb-2 btn-secondary">
                                                <span class="fe fe-plus-square fe-16 mr-2"></span>
                                                {{ trans('admin.add_question') }}
                                            </a>
                                        </div>
                                    </div>

                                    @if(isset($productType))
                                        <div class="box-margin height-card art-above-all mt-5">
                                            <div class="card">
                                                <div class="card-body">
                                                    <h6 class="card-title mb-1">{{ trans('admin.additional_products') }}</h6>
                                                    <p class="mb-20">{{ trans('admin.add_additional_product') }}</p>

                                                    <div id="art-additional-product-tags" class="art-tags">
                                                        <input type="hidden" name="additional_products" id="additional-product-search-input" value="{{ (!is_null($additionalProductIds)) ? $additionalProductIds : '' }}">
                                                        {{-- place for Tags --}}
                                                        @if(count($additionalProducts))
                                                            @foreach($additionalProducts as $additionalProduct)
                                                                <button class="btn" data-id="{{ $additionalProduct['id'] }}">{{ $additionalProduct['name'] }}
                                                                    <svg height="512px" id="Layer_1" style="enable-background:new 0 0 512 512;" version="1.1" viewBox="0 0 512 512" width="512px" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M443.6,387.1L312.4,255.4l131.5-130c5.4-5.4,5.4-14.2,0-19.6l-37.4-37.6c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4  L256,197.8L124.9,68.3c-2.6-2.6-6.1-4-9.8-4c-3.7,0-7.2,1.5-9.8,4L68,105.9c-5.4,5.4-5.4,14.2,0,19.6l131.5,130L68.4,387.1  c-2.6,2.6-4.1,6.1-4.1,9.8c0,3.7,1.4,7.2,4.1,9.8l37.4,37.6c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1L256,313.1l130.7,131.1  c2.7,2.7,6.2,4.1,9.8,4.1c3.5,0,7.1-1.3,9.8-4.1l37.4-37.6c2.6-2.6,4.1-6.1,4.1-9.8C447.7,393.2,446.2,389.7,443.6,387.1z"/></svg>
                                                                </button>
                                                            @endforeach
                                                        @endif
                                                    </div>

                                                    <div class="art-additional-product-fields">
                                                        <div class="form-group mt-3 doc-search-wrapper">
                                                            <input type="text"
                                                                   class="form-control"
                                                                   id="additional-product-search-input-field"
                                                                   placeholder="{{ trans('admin.find_additional_product') }}"
                                                                   value=""
                                                            >
                                                            <div id="additional-product-search-result" class="doc-search-result d-none"></div>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    @endif


                                    <p class="mt-5">
                                        <strong>
                                            {{ 'SEO' }}
                                        </strong>
                                    </p>
                                    <div class="row">

                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <x-admin.multilanguage-input
                                                        :is-required="true"
                                                        :label="trans('admin.seo_title')"
                                                        field-name="seo_title"
                                                        :values="[]"
                                                        :values="isset($seoData['title']) ? $seoData['title'] : []"/>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <x-admin.multilanguage-text-area
                                                        :is-required="true"
                                                        :label="trans('admin.seo_text')"
                                                        field-name="seo_text"
                                                        :values="isset($seoData['content']) ? $seoData['content'] : []"/>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.product-field.list.page') }}"
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

    @if(isset($productType))
        <script>
            const csrf = '{{ csrf_token() }}';
            const additional_product_search_route = '{{ route('admin.additional-product.search', ['productType' => $productType['id']]) }}';
            const locale = '{{ app()->getLocale() }}';
        </script>
        <script src="/static-admin/js/settings/additional-products-field.js"></script>
    @endif

    <script type='text/javascript'>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            $('#product_type_image_input').change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $('#product_type_image').attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            let highestFieldId = 0;
            $('.field').each(function () {
                const id = parseInt($(this).attr('id').replace('field-id-', ''));
                if (id >= highestFieldId) {
                    highestFieldId = id;
                }

                addFilterCheckBoxHandler(id);
            });

            $('#add-field').click(function (event) {
                event.preventDefault();

                highestFieldId++;
                addField(highestFieldId);
            });

            /* add attributes */
            let highestAttributeId = 0;
            $('.attribute').each(function () {
                const id = parseInt($(this).attr('id').replace('attribute-id-', ''));
                if (id >= highestAttributeId) {
                    highestAttributeId = id;
                }

            });

            $('#add-attribute').click(function (event) {
                event.preventDefault();

                highestAttributeId++;
                addAttribute(highestAttributeId);
            });
            /* add attributes END */


            /* add FAQs */
            let highestFAQsId = 0;
            $('.faq-row').each(function () {
                const id = parseInt($(this).attr('id').replace('faq-id-', ''));
                if (id >= highestFAQsId) {
                    highestFAQsId = id;
                }

            });

            $('#add-question').click(function (event) {
                event.preventDefault();

                highestFAQsId++;
                addQuestion(highestFAQsId);
            });
            /* add FAQs END */


            $('#product_has_size').change(function () {
                if ($(this).is(':checked')) {
                    $('#product-has-size-block').removeClass('d-none');
                } else {
                    $('#product-has-size-block').addClass('d-none');
                }
            });

            $('#product_filter_by_length').change(function () {
                if ($(this).is(':checked')) {
                    $('#length-filter-block').removeClass('d-none');
                } else {
                    $('#length-filter-block').addClass('d-none');
                }
            });

            $('#product_filter_by_width').change(function () {
                if ($(this).is(':checked')) {
                    $('#width-filter-block').removeClass('d-none');
                } else {
                    $('#width-filter-block').addClass('d-none');
                }
            });

            $('#product_filter_by_height').change(function () {
                if ($(this).is(':checked')) {
                    $('#height-filter-block').removeClass('d-none');
                } else {
                    $('#height-filter-block').addClass('d-none');
                }
            });

            loadSlugifyFields();
        });

        function addField(id) {
            $('#fields').append(`
                <div class="row field pb-1" id="field-id-${id}">
                    <div class="col-md-12">
                        <div class="border border-secondary rounded p-3">
                            <div class="row justify-content-between align-items-center">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="field-select-${id}">{{ trans('admin.product_field') }} <strong class="text-danger">*</strong></label>
                                        <select class="form-control select2" id="field-select-${id}" name="product_field[${id}][id]">
                                            <option value="" selected disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field'))  }}</option>
                                            @foreach($productFields as $productField)
            <option value="{{ $productField->id }}">{{ $productField->field_name }}</option>
                                            @endforeach
            </select>
        </div>
        <div class="custom-control custom-checkbox mb-3">
            <input type="hidden" name="product_field[${id}][show_as_filter]" value="0">
                                        <input class="custom-control-input" value="1" type="checkbox" id="field-as-filter-${id}" name="product_field[${id}][show_as_filter]">
                                        <label class="custom-control-label" for="field-as-filter-${id}">{{ trans('admin.show_as_filter') }}</label>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-product_has_category"></div>
                                    </div>
                                    <div class="row" id="field-filter-name-${id}" style="display: none;">
                                        <div class="col-md-12">
                                            <div class="custom-control custom-checkbox mb-3">
                                                <input type="hidden" name="product_field[${id}][show_on_main_filters_list]" value="${id}">
                                                <input class="custom-control-input" value="1" type="checkbox" id="show-field-on-main-filter-list-${id}" name="product_field[${id}][show_on_main_filters_list]">
                                                <label class="custom-control-label" for="show-field-on-main-filter-list-${id}">{{ trans('admin.filter_show_on_main_filters_list') }}</label>
                                            </div>
                                            <x-admin.multilanguage-input :is-required="true" :label="trans('admin.filter_name')" field-name="product_field[${id}][filter_name]" :values="[]"/>
                                            <div class="form-group mb-3">
                                                <label for="field-select-${id}">{{ trans('admin.filter_full_position') }} <strong class="text-danger">*</strong></label>
                                                <select class="form-control select2" id="field-select-${id}" name="product_field[${id}][filter_full_position_id]">
                                                    @foreach(\App\DataClasses\ProductFilterFullPositionOptionsDataClass::get() as $filterFullPosition)
            <option value="{{ $filterFullPosition['id'] }}">{{ $filterFullPosition['name'] }}</option>
                                                    @endforeach
            </select>
        </div>
        <div class="mt-1 text-danger ajaxError" id="error-field-product_has_category"></div>
    </div>
</div>
</div>
<div class="col-md-2">
<a href="#" class="btn btn-danger" onclick="removeField(event, ${id})">
                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                        {{ trans('admin.field_delete') }}
            </a>
        </div>
</div>
</div>
<div class="mt-1 text-danger ajaxError" id="error-field-product_field.${id}.id"></div>
                    </div>
                </div>
        `);

            $(`#field-select-${id}`).select2({
                theme: 'bootstrap4'
            });

            addFilterCheckBoxHandler(id);
        }

        function removeField(event, id) {
            event.preventDefault();

            $(`#field-id-${id}`).remove();
        }

        function addAttribute(id) {
            $('#attributes').append(`
            <div class="row attribute pb-1" id="attribute-id-${id}">
                <div class="col-md-12">
                    <div class="border border-secondary rounded p-3">
                        <div class="row justify-content-between align-items-center">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label for="attribute-select-${id}">{{ trans('admin.product_field') }} <strong class="text-danger">*</strong></label>
                                    <select class="form-control select2" id="attribute-select-${id}" name="product_attribute[${id}][id]">
                                        <option value="" selected disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field'))  }}</option>
                                        @foreach($productAttributes as $productAttribute)
            <option value="{{ $productAttribute->id }}">{{ $productAttribute->attribute_name }}</option>
                                        @endforeach
            </select>
        </div>
    </div>
<div class="col-md-2">
<a href="#" class="btn btn-danger"
onclick="artRemoveAttribute(event, ${id})">
            <span class="fe fe-trash-2 fe-16 mr-2"></span>
{{ trans('admin.field_delete') }}
            </a>
        </div>
    </div>
</div>
<div class="mt-1 text-danger ajaxError"
     id="error-field-product_attribute.${id}.id"></div>
</div>
</div>
        `);

            $(`#attribute-select-${id}`).select2({
                theme: 'bootstrap4'
            });

        }
        function artRemoveAttribute(event, id) {
            event.preventDefault();

            $(`#attribute-id-${id}`).remove();
        }

        function addQuestion(id) {
            $('#faqs').append(`

            <div class="row faq-row pb-1" id="faq-id-${id}">
                <div class="col-md-12">
                    <div class="border border-secondary rounded p-3">
                        <div class="row justify-content-between align-items-center">

                            <div class="col-md-12">
                                <div class="row" id="faq-name-${id}">
                                    <div class="col-md-12">
                                        <x-admin.multilanguage-input
                                            :is-required="true"
                                            :label="trans('admin.faq_question')"
                                            field-name="faqs[${id}][question]"
                                            :values="[]"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="row" id="faq-name-${id}">
                                    <div class="col-md-12">
                                        <x-admin.multilanguage-text-area
                                            :is-required="true"
                                            :label="trans('admin.faq_answer')"
                                            field-name="faqs[${id}][answer]"
                                            :values="[]"/>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <a href="#" class="btn btn-danger"
                                   onclick="artRemoveQuestion(event, ${id})">
                                    <span
                                        class="fe fe-trash-2 fe-16 mr-2"></span>
                                    {{ trans('admin.delete_question') }}
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            `);

        }
        function artRemoveQuestion(event, id) {
            event.preventDefault();

            $(`#faq-id-${id}`).remove();
        }

        function addFilterCheckBoxHandler(id) {
            $('#field-as-filter-' + id).change(function () {
                const filterName = $('#field-filter-name-' + id);
                if ($(this).is(':checked')) {
                    filterName.removeAttr('style');
                } else {
                    filterName.attr('style', 'display: none;')
                }
            });
        }

        function loadSlugifyFields() {
            let CyrLat = new CyrLatConverter().init();

            $('#name_{{$baseLanguage}}').keyup(function () {
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue));
            });
        }


        function slugify(s) {
            return s.toString().normalize('NFD').replace(/[\u0300-\u036f]/g, "") //remove diacritics
                .toLowerCase()
                .replace(/\s+/g, '-') //spaces to dashes
                .replace(/&/g, '-and-') //ampersand to and
                .replace(/[^\w\-]+/g, '') //remove non-words
                .replace(/\-\-+/g, '-') //collapse multiple dashes
                .replace(/^-+/, '') //trim starting dash
                .replace(/-+$/, ''); //trim ending dash
        }

    </script>
@endpush

