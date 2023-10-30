@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($productAttribute)
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
                                               action="{{ isset($productAttribute) ? route('admin.product-attribute.edit', ['productAttribute' => $productAttribute->id])  : route('admin.product-attribute.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <x-admin.multilanguage-input :label="trans('admin.name')"
                                                                 :is-required="true"
                                                                 field-name="product_attribute_name"
                                                                 :values="isset($productAttribute) ? $productAttribute->getTranslations('attribute_name') : []"/>
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control"
                                               @isset($productAttribute) value="{{ $productAttribute->slug }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
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

