@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($productField)
                    <h2 class="page-title">{{ trans('admin.product_field_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.product_field_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.product_field_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST"
                                               action="{{ isset($productField) ? route('admin.product-field.edit', ['productField' => $productField->id])  : route('admin.product-field.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group mb-3">
                                        <label for="product_field_type">{{ trans('admin.product_field_type') }} <strong
                                                class="text-danger">*</strong></label>
                                        @if(isset($optionsInUse) && count($optionsInUse))
                                            <input type="hidden" name="product_field_type" value="{{ $productField->field_type_id }}">
                                        @endif
                                        <select class="form-control select2" id="product_field_type"
                                                name="product_field_type"
                                                @if(isset($optionsInUse) && count($optionsInUse)) disabled @endif>
                                            <option value="" @if(!isset($productField)) selected
                                                    @endif disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.product_field_type'))  }}</option>
                                            @foreach(App\DataClasses\ProductFieldTypeOptionsDataClass::get() as $productFieldType)
                                                <option value="{{ $productFieldType['id'] }}"
                                                        @if (isset($productField) && $productField->field_type_id == $productFieldType['id']) selected @endif>{{ $productFieldType['name'] }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-danger ajaxError"
                                             id="error-field-product_field_type"></div>
                                    </div>
                                    <x-admin.multilanguage-input :label="trans('admin.name')"
                                                                 :is-required="true"
                                                                 field-name="product_field_name"
                                                                 :values="isset($productField) ? $productField->getTranslations('field_name') : []"/>
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control"
                                               @isset($productField) value="{{ $productField->slug }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                                    </div>
                                    <div id="field-type-size" style="display: none;">
                                        <x-admin.multilanguage-input :label="trans('admin.point_name')"
                                                                     field-name="product_field_size_name"
                                                                     :values="isset($productField) ? $productField->getTranslations('field_size_name') : []"/>
                                    </div>
                                    <div id="field-type-option" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="hidden" name="is_multiselectable" value="0">
                                                    <input class="custom-control-input" value="1" type="checkbox"
                                                           id="is_multiselectable" name="is_multiselectable"
                                                           @if(isset($productField) && $productField->is_multiselectable) checked @endif>
                                                    <label class="custom-control-label"
                                                           for="is_multiselectable">{{ trans('admin.is_multiselectable') }}</label>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-is_multiselectable"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="hidden" name="as_image" value="0">
                                                    <input class="custom-control-input" value="1" type="checkbox"
                                                           id="as_image" name="as_image"
                                                           @if(isset($productField) && $productField->as_image) checked @endif>
                                                    <label class="custom-control-label"
                                                           for="as_image">{{ trans('admin.as_image') }}</label>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-as_image"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="custom-control custom-checkbox mb-3">
                                                    <input type="hidden" name="display_on_single" value="0">
                                                    <input class="custom-control-input" value="1" type="checkbox"
                                                           id="display_on_single" name="display_on_single"
                                                           @if(isset($productField) && $productField->display_on_single) checked @endif>
                                                    <label class="custom-control-label"
                                                           for="display_on_single">{{ trans('admin.display_on_single') }}</label>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-display_on_single"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12" id="options">
                                                @if (isset($productField) && count($productField->options))
                                                    @foreach($productField->options as $option)
                                                        <div class="row option pb-1" id="option-id-{{ $option->id }}">
                                                            <div class="col-md-12">
                                                                <div class="border border-secondary rounded p-3">
                                                                    <input type="hidden"
                                                                           name="product_field_option[{{ $option->id }}][id]"
                                                                           value="{{ $option->id }}">
                                                                    <x-admin.multilanguage-input
                                                                        :label="trans('admin.option_name')"
                                                                        field-name="product_field_option[{{ $option->id }}][name]"
                                                                        :values="$option->getTranslations('name')"/>
                                                                    <div class="form-group mb-3">
                                                                        <label
                                                                            for="option-{{ $option->id }}-slug">{{ trans('admin.slug') }}</label>
                                                                        <input type="text"
                                                                               id="option-{{ $option->id }}-slug"
                                                                               name="product_field_option[{{ $option->id }}][slug]"
                                                                               class="form-control"
                                                                               value="{{ $option->slug }}">
                                                                        <div class="mt-1 text-danger ajaxError"
                                                                             id="error-field-product_field_option.{{ $option->id }}.slug"></div>
                                                                    </div>
                                                                    <div class="form-group mt-3 field-option-image"
                                                                         @if(!$productField->as_image) style="display: none;" @endif>
                                                                        <label
                                                                            for="option-{{ $option->id }}-image">{{ trans('admin.product_field_option_image') }}
                                                                            ({{ trans('admin.product_field_image_requirements') }}
                                                                            , jpeg,png,jpg)</label>
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <div class="row">
                                                                                    <div class="col-md-12">
                                                                                        <img
                                                                                            @if(isset($option->image_url)) src="{{ $option->image_url }}"
                                                                                            @else style="display: none;"
                                                                                            @endif id="option-{{ $option->id }}-image"
                                                                                            alt="{{ trans('admin.product_field_option_image') }}"
                                                                                            class="category-img rounded mb-3">
                                                                                    </div>
                                                                                </div>
                                                                                <div class="custom-file">
                                                                                    <input type="file"
                                                                                           class="custom-file-input"
                                                                                           name="product_field_option[{{ $option->id }}][image]"
                                                                                           id="option-{{ $option->id }}-image_input">
                                                                                    <label class="custom-file-label"
                                                                                           for="option-{{ $option->id }}-image">{{ trans('admin.choose_file') }}</label>
                                                                                    <div
                                                                                        class="mt-1 text-danger ajaxError"
                                                                                        id="error-field-{{ 'product_field_option.' . $option->id . '.image' }}"></div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12 text-center">
                                                                            <a href="#"
                                                                               class="btn mb-2 btn-danger @if(in_array($option->id, $optionsInUse)) disabled @endif"
                                                                               onclick="removeOption(event, {{ $option->id }})">
                                                                                <span
                                                                                    class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                                {{ trans('admin.option_delete') }}
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="row option pb-1" id="option-id-0">
                                                        <div class="col-md-12">
                                                            <div class="border border-secondary rounded p-3">
                                                                <input type="hidden" name="product_field_option[0][id]"
                                                                       value="0">
                                                                <x-admin.multilanguage-input
                                                                    :label="trans('admin.option_name')"
                                                                    field-name="product_field_option[0][name]"
                                                                    :values="[]"/>
                                                                <div class="form-group mb-3">
                                                                    <label
                                                                        for="option-0-slug">{{ trans('admin.slug') }}</label>
                                                                    <input type="text" id="option-0-slug"
                                                                           name="product_field_option[0][slug]"
                                                                           class="form-control">
                                                                    <div class="mt-1 text-danger ajaxError"
                                                                         id="error-field-product_field_option.0.slug"></div>
                                                                </div>
                                                                <div class="form-group mt-3 field-option-image"
                                                                     style="display: none;">
                                                                    <label
                                                                        for="option-0-image">{{ trans('admin.product_field_option_image') }}
                                                                        ({{ trans('admin.product_field_image_requirements') }}
                                                                        , jpeg,png,jpg)</label>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="row">
                                                                                <div class="col-md-12">
                                                                                    <img style="display: none;"
                                                                                         id="option-0-image"
                                                                                         alt="{{ trans('admin.product_field_option_image') }}"
                                                                                         class="category-img rounded mb-3">
                                                                                </div>
                                                                            </div>
                                                                            <div class="custom-file">
                                                                                <input type="file"
                                                                                       class="custom-file-input"
                                                                                       name="product_field_option[0][image]"
                                                                                       id="option-0-image_input">
                                                                                <label class="custom-file-label"
                                                                                       for="option-0-image">{{ trans('admin.choose_file') }}</label>
                                                                                <div class="mt-1 text-danger ajaxError"
                                                                                     id="error-field-product_field_option.0.image"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12 text-center">
                                                                        <a href="#" class="btn mb-2 btn-danger"
                                                                           onclick="removeOption(event, 0)">
                                                                            <span
                                                                                class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                            {{ trans('admin.option_delete') }}
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12 mb-3 text-danger ajaxError"
                                                 id="error-field-product_field_option">
                                            </div>
                                        </div>
                                        <div class="row pt-3">
                                            <div class="col-md-12 text-center">
                                                <a href="#" id="add-option" class="btn mb-2 btn-secondary">
                                                    <span class="fe fe-plus-square fe-16 mr-2"></span>
                                                    {{ trans('admin.option_add') }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="field-numeric-filters" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-12" id="numeric-filters">
                                                <div class="form-group mb-3">
                                                    <label
                                                        for="numeric_field_filter_type_id">{{ trans('admin.numeric_field_filter_type_id') }}</label>
                                                    <select class="form-control select2"
                                                            id="numeric_field_filter_type_id"
                                                            name="numeric_field_filter_type_id">
                                                        @foreach(App\DataClasses\NumericFieldFilerTypesDataClass::get() as $numericFiledFilterType)
                                                            <option
                                                                @if(isset($productField) && $productField->numeric_field_filter_type_id == $numericFiledFilterType['id']) selected
                                                                @endif value="{{ $numericFiledFilterType['id'] }}">{{ $numericFiledFilterType['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div class="mt-1 text-danger ajaxError"
                                                         id="error-field-product_field_type"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12" id="numeric-filter-options-block"
                                                 style="display: none;">
                                                <div class="row">
                                                    <div class="col-md-12" id="numeric-filter-options-list">
                                                        @if(isset($productField) && count($productField->fieldFilterOptions))
                                                            @foreach($productField->fieldFilterOptions as $fieldFilterOption)
                                                                <div class="row pb-1 numeric-filter-option"
                                                                     id="numeric-filter-option-id-{{ $fieldFilterOption->id }}">
                                                                    <div class="col-md-12">
                                                                        <div
                                                                            class="border border-secondary rounded p-3">
                                                                            <input type="hidden"
                                                                                   name="numeric_filter_option[{{ $fieldFilterOption->id }}][id]"
                                                                                   value="{{ $fieldFilterOption->id }}">
                                                                            <x-admin.multilanguage-input
                                                                                :label="trans('admin.numeric_filter_option_name') . ' (' . trans('admin.numeric_filter_option_name_explanation') . ')'"
                                                                                field-name="numeric_filter_option[{{ $fieldFilterOption->id }}][name]"
                                                                                :values="$fieldFilterOption->getTranslations('name')"/>
                                                                            <div class="form-group mb-3">
                                                                                <label
                                                                                    for="filter-option-{{ $fieldFilterOption->id }}-slug">{{ trans('admin.slug') }}</label>
                                                                                <input type="text"
                                                                                       id="filter-option-{{ $fieldFilterOption->id }}-slug"
                                                                                       name="numeric_filter_option[{{ $fieldFilterOption->id }}][slug]"
                                                                                       class="form-control"
                                                                                       value="{{ $fieldFilterOption->slug }}">
                                                                                <div class="mt-1 text-danger ajaxError"
                                                                                     id="error-field-numeric_filter_option.{{ $fieldFilterOption->id }}.slug"></div>
                                                                            </div>
                                                                            <div class="form-group mb-3">
                                                                                <label
                                                                                    for="numeric-filter-option-from-{{ $fieldFilterOption->id }}">{{ trans('admin.numeric_filter_option_from') }}
                                                                                    ({{ trans('admin.numeric_filter_option_from_explanations') }}
                                                                                    )</label>
                                                                                <input type="text"
                                                                                       id="numeric-filter-option-from-{{ $fieldFilterOption->id }}"
                                                                                       name="numeric_filter_option[{{ $fieldFilterOption->id }}][from]"
                                                                                       class="form-control"
                                                                                       value="{{ $fieldFilterOption->from }}">
                                                                                <div class="mt-1 text-danger ajaxError"
                                                                                     id="error-field-numeric_filter_option.{{ $fieldFilterOption->id }}.from"></div>
                                                                            </div>
                                                                            <div class="form-group mb-3">
                                                                                <label
                                                                                    for="numeric-filter-option-from-{{ $fieldFilterOption->id }}">{{ trans('admin.numeric_filter_option_to') }}
                                                                                    ({{ trans('admin.numeric_filter_option_to_explanation') }}
                                                                                    )</label>
                                                                                <input type="text"
                                                                                       id="numeric-filter-option-from-{{ $fieldFilterOption->id }}"
                                                                                       name="numeric_filter_option[{{ $fieldFilterOption->id }}][to]"
                                                                                       class="form-control"
                                                                                       value="{{ $fieldFilterOption->to }}">
                                                                                <div class="mt-1 text-danger ajaxError"
                                                                                     id="error-field-numeric_filter_option.{{ $fieldFilterOption->id }}.to"></div>
                                                                            </div>
                                                                            <div class="row">
                                                                                <div class="col-md-12 text-center">
                                                                                    <a href="#"
                                                                                       class="btn mb-2 btn-danger"
                                                                                       onclick="removeNumericFilterOption(event, {{ $fieldFilterOption->id }})">
                                                                                        <span
                                                                                            class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                                        {{ trans('admin.option_delete') }}
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="row pb-1 numeric-filter-option"
                                                                 id="numeric-filter-option-id-0">
                                                                <div class="col-md-12">
                                                                    <div class="border border-secondary rounded p-3">
                                                                        <input type="hidden"
                                                                               name="numeric_filter_option[0][id]"
                                                                               value="0">
                                                                        <x-admin.multilanguage-input
                                                                            :label="trans('admin.numeric_filter_option_name') . ' (' . trans('admin.numeric_filter_option_name_explanation') . ')'"
                                                                            field-name="numeric_filter_option[0][name]"
                                                                            :values="[]"/>
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                for="filter-option-0-slug">{{ trans('admin.slug') }}</label>
                                                                            <input type="text" id="filter-option-0-slug"
                                                                                   name="numeric_filter_option[0][slug]"
                                                                                   class="form-control">
                                                                            <div class="mt-1 text-danger ajaxError"
                                                                                 id="error-field-numeric_filter_option.0.slug"></div>
                                                                        </div>
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                for="numeric-filter-option-from-0">{{ trans('admin.numeric_filter_option_from') }}
                                                                                ({{ trans('admin.numeric_filter_option_from_explanations') }}
                                                                                )</label>
                                                                            <input type="text"
                                                                                   id="numeric-filter-option-from-0"
                                                                                   name="numeric_filter_option[0][from]"
                                                                                   class="form-control">
                                                                            <div class="mt-1 text-danger ajaxError"
                                                                                 id="error-field-numeric_filter_option.0.from"></div>
                                                                        </div>
                                                                        <div class="form-group mb-3">
                                                                            <label
                                                                                for="numeric-filter-option-from-0">{{ trans('admin.numeric_filter_option_to') }}
                                                                                ({{ trans('admin.numeric_filter_option_to_explanation') }}
                                                                                )</label>
                                                                            <input type="text"
                                                                                   id="numeric-filter-option-from-0"
                                                                                   name="numeric_filter_option[0][to]"
                                                                                   class="form-control">
                                                                            <div class="mt-1 text-danger ajaxError"
                                                                                 id="error-field-numeric_filter_option.0.to"></div>
                                                                        </div>
                                                                        <div class="row">
                                                                            <div class="col-md-12 text-center">
                                                                                <a href="#" class="btn mb-2 btn-danger"
                                                                                   onclick="removeNumericFilterOption(event, 0)">
                                                                                    <span
                                                                                        class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                                    {{ trans('admin.option_delete') }}
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row pt-3">
                                                    <div class="col-md-12 text-center">
                                                        <a href="#" id="add-numeric-filter-option"
                                                           class="btn mb-2 btn-secondary">
                                                            <span class="fe fe-plus-square fe-16 mr-2"></span>
                                                            {{ trans('admin.option_add') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div> <!-- /.col -->
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
                </div> <!-- / .card -->
            </div> <!-- .col-12 -->
        </div> <!-- .row -->
    </div> <!-- .container-fluid -->
@endsection
@push('scripts')
    <script src="/static-admin/js/cyrillic-lat-conventer.js"></script>
    <script src="/static-admin/js/jquery-helpers.js"></script>
    <script type='text/javascript'>
        $(document).ready(function () {
            let highestOptionId = 0;
            let highestNumericFilterOptionId = 0;

            $('.option').each(function () {
                const id = parseInt($(this).attr('id').replace('option-id-', ''));
                if (id >= highestOptionId) {
                    highestOptionId = id;
                }

                //add image handler
                addOptionImagePreviewHandler(id);


                addOptionSlugHandler(id);
            });

            $('.numeric-filter-option').each(function () {
                const id = parseInt($(this).attr('id').replace('numeric-filter-option-id-', ''));
                if (id >= highestNumericFilterOptionId) {
                    highestNumericFilterOptionId = id;
                }

                addNumericFilterOptionsSlugHandler(id);
            });

            const productFieldType = $('#product_field_type');
            const numericFiledFilterType = $('#numeric_field_filter_type_id');

            handleSelectedOption(productFieldType.val());
            handleSelectedNumericFilterType(numericFiledFilterType.val());

            productFieldType.change(function () {
                hideAll();
                const value = $(this).val();

                handleSelectedOption(value);
            });

            numericFiledFilterType.change(function () {
                hideNumericFilterType();
                const value = $(this).val();

                handleSelectedNumericFilterType(value);
            })

            $('#add-option').click(function (event) {
                event.preventDefault();

                highestOptionId++;
                addOption(highestOptionId, $('#as_image').is(':checked'));
            });

            $('#add-numeric-filter-option').click(function (event) {
                event.preventDefault();

                highestNumericFilterOptionId++;
                addNumericFilterOption(highestNumericFilterOptionId);
            })

            $('#as_image').click(function () {
                if ($(this).is(':checked')) {
                    showOptionsImageField();
                } else {
                    hideOptionsImageField();
                }
            });

            $('.select2').select2({
                theme: 'bootstrap4'
            });

            loadSlugifyFields();
        });

        function loadSlugifyFields() {
            $('#product_field_name_{{$baseLanguage}}').keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue));
            });
        }

        function handleSelectedNumericFilterType(value) {
            switch (value) {
                case '{{ App\DataClasses\NumericFieldFilerTypesDataClass::NUMERIC_FILTER_AS_OPTIONS_TYPE }}':
                    showNumericFilterOptionsType();
                    break;
                default:
                    break;
            }
        }

        function addNumericFilterOptionsSlugHandler(id) {
            $(`#numeric_filter_option\\[${id}\\]\\[name\\]_{{$baseLanguage}}`).keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $(`#filter-option-${id}-slug`).val(slugify(latValue))
            });
        }

        function showNumericFilterOptionsType() {
            $('#numeric-filter-options-block').removeAttr('style');
        }

        function hideNumericFilterType() {
            $('#numeric-filter-options-block').attr('style', 'display: none;');
        }

        function removeNumericFilterOption(event, id) {
            event.preventDefault();
            $(`#numeric-filter-option-id-${id}`).remove();
        }

        function addNumericFilterOption(id) {
            $('#numeric-filter-options-list').append(`
                <div class="row pb-1" id="numeric-filter-option-id-${id}">
                    <div class="col-md-12">
                        <div class="border border-secondary rounded p-3">
                            <input type="hidden" name="numeric_filter_option[${id}][id]" value="${id}">
                            <x-admin.multilanguage-input :label="trans('admin.numeric_filter_option_name') . ' (' . trans('admin.numeric_filter_option_name_explanation') . ')'" field-name="numeric_filter_option[${id}][name]" :values="[]"/>
                            <div class="form-group mb-3">
                                <label for="filter-option-${id}-slug">{{ trans('admin.slug') }}</label>
                                <input type="text" id="filter-option-${id}-slug" name="numeric_filter_option[${id}][slug]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-numeric_filter_option.${id}.slug"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="numeric-filter-option-from-${id}">{{ trans('admin.numeric_filter_option_from') }} ({{ trans('admin.numeric_filter_option_from_explanations') }})</label>
                                <input type="text" id="numeric-filter-option-from-${id}" name="numeric_filter_option[${id}][from]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-numeric_filter_option.${id}.from"></div>
                            </div>
                             <div class="form-group mb-3">
                                <label for="numeric-filter-option-from-${id}">{{ trans('admin.numeric_filter_option_to') }} ({{ trans('admin.numeric_filter_option_to_explanation') }})</label>
                                <input type="text" id="numeric-filter-option-from-${id}" name="numeric_filter_option[${id}][to]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-numeric_filter_option.${id}.to"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <a href="#" class="btn mb-2 btn-danger" onclick="removeNumericFilterOption(event, ${id})">
                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                        {{ trans('admin.option_delete') }}
            </a>
        </div>
    </div>
</div>
</div>
</div>
`);

            addNumericFilterOptionsSlugHandler(id);
        }

        function handleSelectedOption(value) {
            switch (value) {
                case '{{ App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER }}':
                    showNumericFieldFilterOptions();
                    break;
                case '{{ App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE }}':
                    showSizeField();
                    showNumericFieldFilterOptions();
                    break;
                case '{{ App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION }}':
                    showOptionField();
                    break;
                default:
                    break;
            }
        }

        function showSizeField() {
            $('#field-type-size').removeAttr('style');
        }

        function showOptionField() {
            $('#field-type-option').removeAttr('style');
        }

        function showNumericFieldFilterOptions() {
            $('#field-numeric-filters').removeAttr('style');
        }

        function hideAll() {
            $('#field-type-size').attr('style', 'display: none;');
            $('#field-type-option').attr('style', 'display: none;');
            $('field-numeric-filters').attr('style', 'display: none;');
        }

        function addOption(id, asImage) {
            $('#options').append(`
                    <div class="row option pb-1" id="option-id-${id}">
                    <div class="col-md-12">
                        <div class="option border border-secondary rounded p-3">
                            <input type="hidden" name="product_field_option[${id}][id]" value="${id}">
                            <x-admin.multilanguage-input :label="trans('admin.option_name')" field-name="product_field_option[${id}][name]" :values="[]"/>
                            <div class="form-group mb-3">
                                <label for="option-${id}-slug">{{ trans('admin.slug') }}</label>
                                <input type="text" id="option-${id}-slug" name="product_field_option[${id}][slug]" class="form-control">
                                <div class="mt-1 text-danger ajaxError" id="error-field-product_field_option.${id}.slug"></div>
                            </div>
                            <div class="form-group mt-3 field-option-image" ${!asImage ? 'style="display: none;"' : ''}>
                            <label for="option-${id}-image">{{ trans('admin.product_field_option_image') }} ({{ trans('admin.product_field_image_requirements') }}, jpeg,png,jpg)</label>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <img style="display: none;" id="option-${id}-image" alt="{{ trans('admin.product_field_option_image') }}" class="category-img rounded mb-3">
                                            </div>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="product_field_option[${id}][image]" id="option-${id}-image_input">
                                            <label class="custom-file-label" for="option-${id}-image">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-product_field_option.${id}.image"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-center">
                                    <a href="#" class="btn mb-2 btn-danger" onclick="removeOption(event, ${id})">
                                        <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                        {{ trans('admin.option_delete') }}
            </a>
        </div>
    </div>
</div>
</div>
</div>
`);

            const languageCode = $('.multilang-switch.active').attr('href').substring(1);

            $(`#option-id-${id} .multilang-content`).removeClass('active').removeClass('show').each(function () {
                if ($(this).attr('language') === languageCode) {
                    $(this).addClass('active').addClass('show');
                }
            });

            addOptionImagePreviewHandler(id);
            addOptionSlugHandler(id);
        }

        function removeOption(event, id) {
            event.preventDefault();
            $(`#option-id-${id}`).remove();
        }

        function showOptionsImageField() {
            $('.field-option-image').removeAttr('style');
        }

        function hideOptionsImageField() {
            $('.field-option-image').attr('style', 'display: none;');
        }

        function addOptionImagePreviewHandler(id) {
            $(`#option-${id}-image_input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#option-${id}-image`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });
        }

        function addOptionSlugHandler(id) {
            const field = $(`#product_field_option\\[${id}\\]\\[name\\]_{{$baseLanguage}}`);
            field.keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $(`#option-${id}-slug`).val(slugify(latValue))
            });
        }
    </script>
@endpush

