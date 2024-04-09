@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($productCategory)
                    <h2 class="page-title">{{ trans('admin.product_category_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.product_category_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.product_category_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($productCategory) ? route('admin.product-category.edit', ['productCategory' => $productCategory['id'], 'productType' => $productType]) : route('admin.product-category.create', ['productType' => $productType]) }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.name')" field-name="name" :values="isset($productCategory) ? $productCategory->getTranslations('name') : []"/>
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control" @isset($productCategory) value="{{ $productCategory['slug'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                                    </div>

                                    <!-- META TITLE -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_title')" :is-required="false"
                                                                 field-name="meta_title"
                                                                 :values="isset($productCategory) ? $productCategory->getTranslations('meta_title') : []"/>
                                    <!-- META DESCRIPTION -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_description')"
                                                                 :is-required="false" field-name="meta_description"
                                                                 :values="isset($productCategory) ? $productCategory->getTranslations('meta_description') : []"/>
                                    <!-- META KEY WORDS -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_keywords')"
                                                                 :is-required="false" field-name="meta_keywords"
                                                                 :values="isset($productCategory) ? $productCategory->getTranslations('meta_keywords') : []"/>

                                    <div class="form-group">
                                        <label for="category_image">{{ trans('admin.product_category_image') }} ({{ trans('admin.product_category_image_requirements') }}, jpeg,png,jpg)</label>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <img @if(isset($productCategory) && isset($productCategory->image_url)) src="{{ $productCategory->image_url }}" @else style="display: none;" @endif id="category_image" alt="{{ trans('admin.product_category_image') }}" class="category-img rounded mb-3">
                                            </div>
                                        </div>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="category_image" id="category_image_input">
                                            <label class="custom-file-label" for="category_image">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-category_image"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.product-category.list-by-product-type.page', ['productType' => $productType->id]) }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
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
        $(document).ready(function() {
            $('#category_image_input').change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $('#category_image').attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $(`#name_{{$baseLanguage}}`).keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue))
            });
        });
    </script>
@endpush

