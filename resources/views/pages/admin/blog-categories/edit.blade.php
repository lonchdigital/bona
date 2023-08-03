@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @isset($blogCategory)
                    <h2 class="page-title">{{ trans('admin.blog_category_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.blog_category_new') }}</h2>
                @endisset
                <div class="card shadow mb-4">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.blog_category_information') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ isset($blogCategory) ?  route('admin.blog-category.edit', ['blogCategory' => $blogCategory->id]) : route('admin.blog-category.create') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <x-admin.multilanguage-input :is-required="true" :label="trans('admin.name')" field-name="name" :values="isset($blogCategory) ? $blogCategory->getTranslations('name') : []"/>
                                </div>
                            </div>

                            <!-- SLUG -->
                            <div class="form-group mb-3">
                                <label for="code">{{ trans('admin.slug') }} <strong
                                        class="text-danger">*</strong></label>
                                <input type="text" id="slug" name="slug" class="form-control"
                                       @isset($blogCategory) value="{{ $blogCategory->slug }}" @endisset>
                                <div class="mt-1 text-danger ajaxError" id="error-field-slug"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-right">
                                    <a href="{{ route('admin.blog-category.list.page') }}" class="btn btn-secondary">{{ trans('admin.back') }}</a>
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

        $(document).ready(function () {
            $('#name_{{ $baseLanguage }}').keyup(function () {
                const CyrLat = new CyrLatConverter().init();
                const value = $(this).val();
                const latValue = CyrLat.getC2L(value);
                $('#slug').val(slugify(latValue))
            });
        });
    </script>
@endpush

