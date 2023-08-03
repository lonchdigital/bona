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
                    <div class="card-header d-flex align-items-center justify-content-between">
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
                            <div class="form-group mb-3">
                                <label for="color">{{ trans('admin.color') }} <strong class="text-danger">*</strong></label>
                                <input class="form-control" id="color" type="color" name="hex" @isset($color) value="{{ $color->hex }}" @endisset @if(isset($color) && !$color->hex) disabled @endif>
                                <div class="mt-1 text-danger ajaxError" id="error-field-color"></div>
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

        });
    </script>
@endpush

