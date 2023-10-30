@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($productSubtype)
                    <h2 class="page-title">{{ trans('admin.edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.create') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.product_type_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST"
                                               action="{{ isset($productSubtype) ? route('admin.product-subtype.edit', ['productSubtype' => $productSubtype['id']])  : route('admin.product-subtype.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">

                                    <p>
                                        <strong>
                                            {{ trans('admin.product_type_base_info') }}
                                        </strong>
                                    </p>

                                    <!-- PRODUCT NAME -->
                                    <x-admin.multilanguage-input :placeholder="trans('admin.product_subtype_name_example')"
                                                                 :label="trans('admin.name')" :is-required="true"
                                                                 field-name="name"
                                                                 :values="isset($productSubtype) ? $productSubtype->getTranslations('name') : []"/>

                                    <!-- SLUG -->
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong
                                                class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control"
                                               @isset($productSubtype) value="{{ $productSubtype['slug'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
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

