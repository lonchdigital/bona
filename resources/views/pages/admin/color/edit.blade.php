@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($color)
                    <h2 class="page-title">{{ trans('admin.color_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.color_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.color_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($color) ?  route('admin.color.edit', ['color' => $color->id]) : route('admin.color.create') }}">
                            @csrf
                            <x-admin.multilanguage-input :is-required="true" :label="trans('admin.color_name')" field-name="name" :values="isset($color) ? $color->getTranslations('name') : []"/>
                            <div class="form-group mb-3">
                                <label for="slug">{{ trans('admin.slug') }} <strong class="text-danger">*</strong></label>
                                <input type="text" id="slug" name="slug" class="form-control"  @isset($color) value="{{ $color->slug }}" @endisset>
                                <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                            </div>
                            <div class="form-group mb-3">
                                <label for="parent-color-select">{{ trans('admin.parent_color') }}</label>
                                <select class="form-control select2 custom-select2"
                                        name="parent_color_id"
                                        id="parent-color-select">
                                    <option value="" @if(!isset($color)) selected
                                            @endif>{{ trans('admin.select') }} {{ trans('admin.parent_color') }}</option>
                                    @foreach($parentColors as $parentColor)
                                        <option
                                            @if(isset($color) && $color->parent_color == $parentColor->id) selected
                                            @endif value="{{ $parentColor->id }}">{{ $parentColor->name }}</option>
                                    @endforeach
                                </select>
                                <div class="mt-1 text-danger ajaxError" id="error-field-parent_color_id"></div>
                            </div>


                            <br>
                            <div
                                class="custom-control custom-checkbox mb-3">
                                <input type="hidden"
                                       name="display_as_image"
                                       value="0">
                                <input class="custom-control-input"
                                       value="1" type="checkbox"
                                       id="display-as-image"
                                       name="display_as_image"
                                       @if(isset($color) && $color->display_as_image) checked @endif>
                                <label class="custom-control-label"
                                       for="display-as-image">{{ trans('admin.show_as_image') }}</label>
                            </div>


                            <div id="color-hex-field" class="form-group mb-3" @if(isset($color) && $color->display_as_image) style="display: none" @endif>
                                <label for="color">{{ trans('admin.color') }} <strong class="text-danger">*</strong></label>
                                <input class="form-control" id="color" type="color" name="hex" @isset($color) value="{{ $color->hex }}" @endisset @if(isset($color) && !$color->hex) disabled @endif>
                                <div class="mt-1 text-danger ajaxError" id="error-field-color"></div>
                            </div>


                            <!-- COLOR MAIN IMAGE -->
                            <div id="color-main-image" class="form-group mb-3" @if(isset($color) && $color->display_as_image) style="display: block" @else style="display: none" @endif>
                                <div class="row">
                                    <div class="col-md-12">
                                        <label for="main_image">{{ trans('admin.color_main_image') }}</label>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <img
                                            @if(isset($color) && isset($color->main_image)) src="{{ $color->image_url }}"
                                            @else style="display: none;" @endif id="main_image_preview"
                                            alt="{{ trans('admin.color_main_image') }}"
                                            class="category-img rounded mb-3">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-2">
                                        <div class="custom-file">
                                            <input type="hidden" name="main_image_deleted_input"
                                                   id="main_image_deleted_input" value="0">
                                            <input type="file" class="custom-file-input image-input"
                                                   name="main_image" id="main_image">
                                            <label class="custom-file-label"
                                                   for="main_image">{{ trans('admin.choose_file') }}</label>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-main_image"></div>
                                            <div class="mt-1 text-danger ajaxError"
                                                 id="error-field-main_image_deleted_input"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.color.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
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
            const field = $(`#name_{{$baseLanguage}}`);
            field.keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $(`#slug`).val(slugify(latValue))
            });

            $('.select2').select2({
                theme: 'bootstrap4',
            });

            loadImagesHandlers();
            addFilterCheckBoxHandler();
        });


        function loadImagesHandlers() {
            $('.image-input').each(function () {
                const id = $(this).attr('id');
                const previewId = id + '_preview';
                const deleteButtonId = id + '_delete';
                const deletedInputId = id + '_deleted_input';

                const input = $(this);
                const previewImage = $('#' + previewId);
                const deleteButton = $('#' + deleteButtonId);
                const deletedInput = $('#' + deletedInputId);

                $(this).change(function () {
                    const [file] = $(this).prop('files');
                    if (file) {
                        previewImage.attr('src', URL.createObjectURL(file)).attr('style', '');
                        deleteButton.attr('style', '');
                        deletedInput.val(0);
                    }
                });

                deleteButton.click(function (event) {
                    event.preventDefault();
                    previewImage.attr('src', '').attr('style', 'display: none;');
                    $(this).attr('style', 'display: none;');
                    input.val();
                    deletedInput.val(1);
                });
            });
        }


        function addFilterCheckBoxHandler() {
            $('#display-as-image').change(function () {
                const imageField = $('#color-main-image');
                const hexField = $('#color-hex-field');

                if ($(this).is(':checked')) {
                    imageField.removeAttr('style');
                    hexField.attr('style', 'display: none;')
                } else {
                    imageField.attr('style', 'display: none;')
                    hexField.removeAttr('style');
                }

            });
        }


    </script>
@endpush

