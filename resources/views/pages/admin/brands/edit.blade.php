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
                    <div class="card-header d-flex align-items-center justify-content-between">
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
                                    <x-admin.multilanguage-text-area :is-required="true" :label="trans('admin.brand_description')" field-name="description" :values="isset($brand) ? $brand->getTranslations('description') : []"/>
                                    <div class="form-group mb-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="logo">{{ trans('admin.brand_logo') }} <strong class="text-danger">*</strong> ({{ trans('admin.brand_logo_image_requirements') }}, jpeg,png,jpg)</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                @isset($brand)
                                                    <img src="{{ $brand->logo_image_url }}" id="logo-preview" alt="{{ trans('admin.brand_logo') }}" class="category-img rounded mb-3">
                                                @else
                                                    <img src="" style="display: none;" id="logo-preview" alt="{{ trans('admin.brand_logo') }}" class="category-img rounded mb-3">
                                                @endisset
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
                                    <div class="form-group mb-3">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label for="head">{{ trans('admin.brand_head') }} <strong class="text-danger">*</strong> ({{ trans('admin.brand_head_image_requirements') }}, jpeg,png,jpg)</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                @isset($brand)
                                                    <img src="{{ $brand->head_image_url }}" id="head-preview" alt="{{ trans('admin.brand_head') }}" class="brand-head-img rounded mb-3">
                                                @else
                                                    <img src="" style="display: none;" id="head-preview" alt="{{ trans('admin.brand_head') }}" class="brand-head-img rounded mb-3">
                                                @endisset
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="custom-file">
                                                    <input type="file" class="custom-file-input" name="head" id="head" accept="image/jpeg, image/jpg, image/png">
                                                    <label class="custom-file-label" for="head">{{ trans('admin.choose_file') }}</label>
                                                    <div class="mt-1 text-danger ajaxError" id="error-field-head"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <p>
                                        <strong>
                                            {{ trans('admin.brand_slider') }}
                                        </strong>
                                    </p>
                                    <!-- MAIN TEXT -->
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.brand_slider_main_text')" field-name="slider_main_text" :values="isset($brand) ? $brand->getTranslations('slider_main_text') : []"/>

                                    <!-- DESCRIPTION TEXT -->
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.brand_slider_description_text')" field-name="slider_description_text" :values="[]" :values="isset($brand) ? $brand->getTranslations('slider_description_text') : []"/>

                                    <p>
                                        <strong>
                                            {{ trans('admin.brand_slider_images') }}
                                        </strong>
                                    </p>

                                    <div class="row">
                                        <div class="col-md-12" id="slides-block">
                                            @if(isset($brand) && count($brand->slides))
                                                @foreach($brand->slides as $slide)
                                                    <!-- default slide start -->
                                                    <div class="border border-secondary rounded p-3 mb-3 slide" id="slide-id-{{ $slide->id }}">
                                                        <input type="hidden" name="slide[{{ $slide->id }}][id]" value="{{ $slide->id }}">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <!-- image start -->
                                                                <div class="form-group mb-3">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <label for="logo">{{ trans('admin.brand_slide_image') }} <strong class="text-danger">*</strong> ({{ trans('admin.brand_slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            @isset($slide->image_url)
                                                                                <img src="{{ $slide->image_url }}" id="slide-{{$slide->id}}-image-preview" alt="{{ trans('admin.brand_slide_image') }}" class="category-img rounded mb-3">
                                                                            @else
                                                                                <img src="" style="display: none;" id="slide-0-image-preview" alt="{{ trans('admin.brand_slide_image') }}" class="category-img rounded mb-3">
                                                                            @endisset
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="custom-file">
                                                                                <input type="file" class="custom-file-input" name="slide[{{ $slide->id }}][image]" id="slide-{{ $slide->id }}-image-input" accept="image/jpeg, image/jpg, image/png">
                                                                                <label class="custom-file-label" for="slide-{{ $slide->id }}-image-input">{{ trans('admin.choose_file') }}</label>
                                                                                <div class="mt-1 text-danger ajaxError" id="error-field-slide.{{ $slide->id }}.image"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- image end -->
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12 text-center">
                                                                <a href="#" class="btn mb-2 btn-danger" onclick="removeSlide(event, {{ $slide->id }})">
                                                                    <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                    {{ trans('admin.slide_delete') }}
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- default slide end -->
                                                @endforeach
                                            @else
                                                <!-- default slide start -->
                                                <div class="border border-secondary rounded p-3 mb-3 slide" id="slide-id-0">
                                                    <input type="hidden" name="slide[0][id]">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <!-- image 1 start -->
                                                            <div class="form-group mb-3">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label for="logo">{{ trans('admin.brand_slide_image') }} <strong class="text-danger">*</strong> ({{ trans('admin.brand_slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <img src="" style="display: none;" id="slide-0-image-preview" alt="{{ trans('admin.brand_slide_image') }}" class="category-img rounded mb-3">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="custom-file">
                                                                            <input type="file" class="custom-file-input" name="slide[0][image]" id="slide-0-image-input" accept="image/jpeg, image/jpg, image/png">
                                                                            <label class="custom-file-label" for="slide-0-image-input">{{ trans('admin.choose_file') }}</label>
                                                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide.0.image"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- image 1 end -->
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12 text-center">
                                                            <a href="#" class="btn mb-2 btn-danger" onclick="removeSlide(event, 0)">
                                                                <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                                                {{ trans('admin.slide_delete') }}
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- default slide end -->
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide"></div>
                                        </div>
                                    </div>
                                    <div class="row pt-3">
                                        <div class="col-md-12 text-center">
                                            <a href="#" id="add-slide" class="btn mb-2 btn-secondary">
                                                <span class="fe fe-plus-square fe-16 mr-2"></span>
                                                {{ trans('admin.slide_add') }}
                                            </a>
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
            $('#head').change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $('#head-preview').attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $('#name_{{ $baseLanguage }}').keyup(function () {
                $(availableLanguagesExceptBaseLanguage.join(', ')).val($(this).val());
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue))
            });

            let highestSlideId = 0;
            $('.slide').each(function () {
                const id = parseInt($(this).attr('id').replace('slide-id-', ''));
                if (id >= highestSlideId) {
                    highestSlideId = id;
                }

                //add image handler
                addSlideImagePreviewHandler(id);
            });



            $('#add-slide').click(function (event) {
                event.preventDefault();

                highestSlideId++;
                addSlide(highestSlideId);

            });
        });

        function addSlide(id) {
            $('#slides-block').append(`
                 <div class="border border-secondary rounded p-3 mb-3 slide" id="slide-id-${id}">
                    <input type="hidden" name="slide[${id}][id]">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- image 1 start -->
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="logo">{{ trans('admin.brand_slide_image') }} <strong class="text-danger">*</strong> ({{ trans('admin.brand_slide_image_requirements') }}, jpeg,png,jpg)</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <img src="" style="display: none;" id="slide-${id}-image-preview" alt="{{ trans('admin.brand_slide_image') }}" class="category-img rounded mb-3">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="slide[${id}][image]" id="slide-${id}-image-input" accept="image/jpeg, image/jpg, image/png">
                                            <label class="custom-file-label" for="slide-${id}-image-input">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide.${id}.image"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- image 1 end -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <a href="#" class="btn mb-2 btn-danger" onclick="removeSlide(event, ${id})">
                                <span class="fe fe-trash-2 fe-16 mr-2"></span>
                                {{ trans('admin.slide_delete') }}
                            </a>
                        </div>
                    </div>
                </div>
            `);
            addSlideImagePreviewHandler(id);
        }

        function removeSlide(event, id) {
            event.preventDefault();
            $(`#slide-id-${id}`).remove();
        }

        function addSlideImagePreviewHandler(id)
        {
            $(`#slide-${id}-image-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#slide-${id}-image-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });
        }
    </script>
@endpush

