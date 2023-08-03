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

                                    <p class="mt-5">
                                        <strong>
                                            {{ trans('admin.product_type_seo_config') }}
                                        </strong>
                                    </p>

                                    <!-- PRODUCT TYPE META TITLE -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_title')" :is-required="true"
                                                                 field-name="meta_title"
                                                                 :values="isset($productType) ? $productType->getTranslations('meta_title') : []"/>
                                    <!-- PRODUCT TYPE META DESCRIPTION -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_description')"
                                                                 :is-required="true" field-name="meta_description"
                                                                 :values="isset($productType) ? $productType->getTranslations('meta_description') : []"/>
                                    <!-- PRODUCT TYPE META KEY WORDS -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_keywords')"
                                                                 :is-required="true" field-name="meta_keywords"
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
    <script type='text/javascript'>
        $(document).ready(function () {
            $('.select2').select2({
                theme: 'bootstrap4'
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

