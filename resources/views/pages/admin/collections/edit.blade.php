@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($collection)
                    <h2 class="page-title">{{ trans('admin.collection_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.collection_new') }}</h2>
                @endif
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.collection_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($collection) ? route('admin.collection.edit', ['collection' => $collection->id]) : route('admin.collection.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.collection_name') . ' (' . trans('admin.collection_name_explanation') .')'" field-name="name" :values="isset($collection) ? $collection->getTranslations('name') : []"/>
                                    <div class="form-group mb-3">
                                        <label for="slug">{{ trans('admin.slug') }} <strong class="text-danger">*</strong></label>
                                        <input type="text" id="slug" name="slug" class="form-control" @isset($collection) value="{{ $collection['slug'] }}" @endisset>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="brand_id">{{ trans('admin.brand') }} <strong class="text-danger">*</strong></label>
                                        <select class="form-control select2" name="brand_id" id="brand_id">
                                            <option value="" @if(!isset($collection)) selected @endif disabled>{{ trans('admin.select') }}  {{ mb_strtolower(trans('admin.brand'))  }}</option>
                                            @foreach($brands as $brand)
                                                <option value="{{ $brand->id }}" @if(isset($collection) && $collection->brand_id === $brand->id) selected @endif>{{ $brand->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="mt-1 text-danger ajaxError" id="error-field-brand_id"></div>
                                    </div>

                                    <div class="row">
                                        <!-- collection preview 1 start -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="logo">{{ trans('admin.collection_preview_1') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if(isset($collection) && isset($collection->preview_image_1_url))
                                                            <img src="{{ $collection->preview_image_1_url }}" id="collection-preview-image-1-preview" alt="{{ trans('admin.collection_preview_1') }}" class="category-img rounded mb-3">
                                                        @else
                                                            <img src="" style="display: none;" id="collection-preview-image-1-preview" alt="{{ trans('admin.collection_preview_1') }}" class="category-img rounded mb-3">
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="collection_preview_image_1" id="collection-preview-image-1-input" accept="image/jpeg, image/jpg, image/png">
                                                            <label class="custom-file-label" for="collection-preview-image-1-input">{{ trans('admin.choose_file') }}</label>
                                                            <div class="mt-1 text-danger ajaxError" id="error-field-collection_preview_image_1"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- collection preview 1 end -->

                                        <!-- collection preview 2 start -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="logo">{{ trans('admin.collection_preview_2') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="col-md-12">
                                                            @if(isset($collection) && isset($collection->preview_image_2_url))
                                                                <img src="{{ $collection->preview_image_2_url }}" id="collection-preview-image-2-preview" alt="{{ trans('admin.collection_preview_2') }}" class="category-img rounded mb-3">
                                                            @else
                                                                <img src="" style="display: none;" id="collection-preview-image-2-preview" alt="{{ trans('admin.collection_preview_2') }}" class="category-img rounded mb-3">
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="collection_preview_image_2" id="collection-preview-image-2-input" accept="image/jpeg, image/jpg, image/png">
                                                            <label class="custom-file-label" for="collection-preview-image-2-input">{{ trans('admin.choose_file') }}</label>
                                                            <div class="mt-1 text-danger ajaxError" id="error-field-collection_preview_image_2"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- collection preview 2 end -->

                                        <!-- collection preview 3 start -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="logo">{{ trans('admin.collection_preview_3') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if(isset($collection) && isset($collection->preview_image_3_url))
                                                            <img src="{{ $collection->preview_image_3_url }}" id="collection-preview-image-3-preview" alt="{{ trans('admin.collection_preview_3') }}" class="category-img rounded mb-3">
                                                        @else
                                                            <img src="" style="display: none;" id="collection-preview-image-3-preview" alt="{{ trans('admin.collection_preview_3') }}" class="category-img rounded mb-3">
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="collection_preview_image_3" id="collection-preview-image-3-input" accept="image/jpeg, image/jpg, image/png">
                                                            <label class="custom-file-label" for="collection-preview-image-3-input">{{ trans('admin.choose_file') }}</label>
                                                            <div class="mt-1 text-danger ajaxError" id="error-field-collection_preview_image_3"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- collection preview 3 end -->

                                        <!-- collection preview 4 start -->
                                        <div class="col-md-3">
                                            <div class="form-group mb-3">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <label for="logo">{{ trans('admin.collection_preview_4') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if(isset($collection) && isset($collection->preview_image_4_url))
                                                            <img src="{{ $collection->preview_image_4_url }}" id="collection-preview-image-4-preview" alt="{{ trans('admin.collection_preview_3') }}" class="category-img rounded mb-3">
                                                        @else
                                                            <img src="" style="display: none;" id="collection-preview-image-4-preview" alt="{{ trans('admin.collection_preview_3') }}" class="category-img rounded mb-3">
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input" name="collection_preview_image_4" id="collection-preview-image-4-input" accept="image/jpeg, image/jpg, image/png">
                                                            <label class="custom-file-label" for="collection-preview-image-4-input">{{ trans('admin.choose_file') }}</label>
                                                            <div class="mt-1 text-danger ajaxError" id="error-field-collection_preview_image_4"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- collection preview 4 end -->
                                    </div>


                                    <p class="mt-5">
                                        <strong>
                                            {{ trans('admin.slides') }}
                                        </strong>
                                    </p>
                                    <div class="row">
                                        <div class="col-md-12" id="slides-block">
                                            @if(isset($collection) && $collection->slides)
                                                @foreach($collection->slides as $slide)
                                                    <!-- default slide start -->
                                                    <div class="border border-secondary rounded p-3 mb-3 slide" id="slide-id-{{ $slide->id }}">
                                                        <input type="hidden" name="slide[{{ $slide->id }}][id]" value="{{ $slide->id }}">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <!-- image 1 start -->
                                                                <div class="form-group mb-3">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <label for="logo">{{ trans('admin.slide_image_1') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            @isset($slide->image_1_url)
                                                                                <img src="{{ $slide->image_1_url }}" id="slide-{{$slide->id}}-image-1-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                                                            @else
                                                                                <img src="" style="display: none;" id="slide-0-image-1-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                                                            @endisset
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="custom-file">
                                                                                <input type="file" class="custom-file-input" name="slide[{{ $slide->id }}][image_1]" id="slide-{{ $slide->id }}-image-1-input" accept="image/jpeg, image/jpg, image/png">
                                                                                <label class="custom-file-label" for="slide-{{ $slide->id }}-image-1-input">{{ trans('admin.choose_file') }}</label>
                                                                                <div class="mt-1 text-danger ajaxError" id="error-field-slide.{{ $slide->id }}.image_1"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- image 1 end -->
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <!-- image 2 start -->
                                                                <div class="form-group mb-3">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <label for="logo">{{ trans('admin.slide_image_2') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            @isset($slide->image_2_url)
                                                                                <img src="{{ $slide->image_2_url }}" id="slide-{{$slide->id}}-image-2-preview" alt="{{ trans('admin.slide_image_2') }}" class="category-img rounded mb-3">
                                                                            @else
                                                                                <img src="" style="display: none;" id="slide-0-image-2-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                                                            @endisset
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="custom-file">
                                                                                <input type="file" class="custom-file-input" name="slide[{{ $slide->id }}][image_2]" id="slide-{{ $slide->id }}-image-2-input" accept="image/jpeg, image/jpg, image/png">
                                                                                <label class="custom-file-label" for="slide-{{ $slide->id }}-image-2-input">{{ trans('admin.choose_file') }}</label>
                                                                                <div class="mt-1 text-danger ajaxError" id="error-field-slide.{{ $slide->id }}.image_2"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <!-- image 2 end -->
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
                                                                        <label for="logo">{{ trans('admin.slide_image_1') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <img src="" style="display: none;" id="slide-0-image-1-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="custom-file">
                                                                            <input type="file" class="custom-file-input" name="slide[0][image_1]" id="slide-0-image-1-input" accept="image/jpeg, image/jpg, image/png">
                                                                            <label class="custom-file-label" for="slide-0-image-1-input">{{ trans('admin.choose_file') }}</label>
                                                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide.0.image_1"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- image 1 end -->
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <!-- image 2 start -->
                                                            <div class="form-group mb-3">
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <label for="logo">{{ trans('admin.slide_image_2') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <img src="" style="display: none;" id="slide-0-image-2-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-12">
                                                                        <div class="custom-file">
                                                                            <input type="file" class="custom-file-input" name="slide[0][image_2]" id="slide-0-image-2-input" accept="image/jpeg, image/jpg, image/png">
                                                                            <label class="custom-file-label" for="slide-0-image-2-input">{{ trans('admin.choose_file') }}</label>
                                                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide.0.image_2"></div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <!-- image 2 end -->
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
                                    <a href="{{ route('admin.collection.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
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

        $('#name_{{ $baseLanguage }}').keyup(function () {
            $(availableLanguagesExceptBaseLanguage.join(', ')).val($(this).val());
        });

        $(document).ready(function() {

            $('.select2').select2({
                theme: 'bootstrap4',
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

            $('#name_{{ $baseLanguage }}').keyup(function () {
                $(availableLanguagesExceptBaseLanguage.join(', ')).val($(this).val());
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue))
            });

            addPreviewImagesPreviewHandler();
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
                                        <label for="logo">{{ trans('admin.slide_image_1') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <img src="" style="display: none;" id="slide-${id}-image-1-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="slide[${id}][image_1]" id="slide-${id}-image-1-input" accept="image/jpeg, image/jpg, image/png">
                                            <label class="custom-file-label" for="slide-0-image-1-input">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide.${id}.image_1"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- image 1 end -->
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <!-- image 2 start -->
                            <div class="form-group mb-3">
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="logo">{{ trans('admin.slide_image_2') }} <strong class="text-danger">*</strong> ({{ trans('admin.slide_image_requirements') }}, jpeg,png,jpg)</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <img src="" style="display: none;" id="slide-${id}-image-2-preview" alt="{{ trans('admin.slide_image_1') }}" class="category-img rounded mb-3">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="slide[${id}][image_2]" id="slide-${id}-image-2-input" accept="image/jpeg, image/jpg, image/png">
                                            <label class="custom-file-label" for="slide-${id}-image-2-input">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError" id="error-field-slide.${id}.image_2"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- image 2 end -->
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
            $(`#slide-${id}-image-1-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#slide-${id}-image-1-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $(`#slide-${id}-image-2-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#slide-${id}-image-2-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });
        }

        function addPreviewImagesPreviewHandler()
        {
            $(`#collection-preview-image-1-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#collection-preview-image-1-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $(`#collection-preview-image-2-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#collection-preview-image-2-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $(`#collection-preview-image-3-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#collection-preview-image-3-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });

            $(`#collection-preview-image-4-input`).change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $(`#collection-preview-image-4-preview`).attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });
        }
    </script>
@endpush
