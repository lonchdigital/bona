@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @if($isCreate)
                    <h2 class="page-title">{{ trans('admin.brand_new') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.brand_edit') }}</h2>
                @endif
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.brand_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($brand) ? route('admin.brand.edit', ['brand' => $brand->id]) : route('admin.brand.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- NAME -->
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.brand_name') . ' (' . trans('admin.brand_name_explanation') .')'" field-name="name" :values="isset($brand) ? $brand->getTranslations('name') : []"/>

                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control" @isset($brand) value="{{ $brand['slug'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                                    </div>

                                    <!-- META TITLE -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_title')" :is-required="false"
                                                                 field-name="meta_title"
                                                                 :values="isset($brand) ? $brand->getTranslations('meta_title') : []"/>
                                    <!-- META DESCRIPTION -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_description')"
                                                                 :is-required="false" field-name="meta_description"
                                                                 :values="isset($brand) ? $brand->getTranslations('meta_description') : []"/>
                                    <!-- META KEY WORDS -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_keywords')"
                                                                 :is-required="false" field-name="meta_keywords"
                                                                 :values="isset($brand) ? $brand->getTranslations('meta_keywords') : []"/>

                                    <x-admin.multilanguage-text-area :is-required="false" :label="trans('admin.brand_description')" field-name="description" :values="isset($brand) ? $brand->getTranslations('description') : []"/>
                                    <div class="form-group mb-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="logo">{{ trans('admin.brand_logo') }} ({{ trans('admin.brand_logo_image_requirements') }}, jpeg,png,jpg)</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                @if(isset($brand) && !is_null($brand->logo_image_path))
                                                    <img src="{{ $brand->logo_image_url }}" id="logo-preview" alt="{{ trans('admin.brand_logo') }}" class="category-img rounded mb-3">
                                                @else
                                                    <img src="" style="display: none;" id="logo-preview" alt="{{ trans('admin.brand_logo') }}" class="category-img rounded mb-3">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="logo" id="logo" accept="image/jpeg, image/jpg, image/png">
                                                    <label class="custom-file-label" for="logo">{{ trans('admin.choose_file') }}</label>
                                                    <div class="mt-1 text-danger ajaxError" id="error-field-logo"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.brand.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
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
        const availableLanguagesExceptBaseLanguage = [
            @foreach(array_diff($availableLanguages, [$baseLanguage]) as $otherLanguage)
            '#name_{{ $otherLanguage }}'
            @endforeach
        ];

        $(document).ready(function() {
            $('#logo').change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $('#logo-preview').attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $('#name_{{ $baseLanguage }}').keyup(function () {
                $(availableLanguagesExceptBaseLanguage.join(', ')).val($(this).val());
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue))
            });

        });
    </script>
@endpush

