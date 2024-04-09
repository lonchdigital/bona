@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($color)
                    <h2 class="page-title">{{ trans('admin.country_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.country_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.country_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($country) ?  route('admin.country.edit', ['country' => $country->id]) : route('admin.country.create') }}">
                            @csrf
                            <x-admin.multilanguage-input :is-required="true" :label="trans('admin.country_name')" field-name="name" :values="isset($country) ? $country->getTranslations('name') : []"/>
                            <div class="form-group mb-3">
                                <label for="code">{{ trans('admin.country_code') }} <strong class="text-danger">*</strong></label>
                                <input type="text" id="code" name="code" class="form-control" @isset($country) value="{{ $country['code'] }}" @endisset>
                                <div class="mt-1 text-danger ajaxError" id="error-field-code"></div>
                            </div>
                            <div class="form-group">
                                <label for="image">{{ trans('admin.country_image') }} <strong class="text-danger">*</strong> ({{ trans('admin.country_image_requirements') }}, svg)</label>
                                <div class="row">
                                    <div class="col-md-12">
                                        <img @if(isset($country) && isset($country->image_url)) src="{{ $country->image_url }}" @else style="display: none;" @endif id="image-preview" alt="{{ trans('admin.country_image') }}" class="country-img rounded mb-3">
                                    </div>
                                </div>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" name="image" id="image_input" accept="image/svg+xml, image/svg">
                                    <label class="custom-file-label" for="image">{{ trans('admin.choose_file') }}</label>
                                    <div class="mt-1 text-danger ajaxError" id="error-field-image"></div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.country.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
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
    <script type='text/javascript'>
        $(document).ready(function() {
            $('#image_input').change(function () {
                const [file] = $(this).prop('files');
                if (file) {
                    $('#image-preview').attr('src', URL.createObjectURL(file)).attr('style', '');
                }
            });
        });
    </script>
@endpush

