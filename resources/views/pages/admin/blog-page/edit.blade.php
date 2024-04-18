@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">

                <h2 class="page-title">{{ trans('admin.blog') }}</h2>

                <div class="card shadow mb-4">
                    <div id="form-header" class="card-header d-flex align-items-center justify-content-between">
                        <strong class="card-title m-0">{{ trans('admin.blog') }}</strong>
                        <x-admin.multilanguage-switch/>
                    </div>
                    <div class="card-body">
                        <x-admin.reactive-form method="POST" action="{{ route('admin.blog-page.edit') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- TITLE -->
                                    <x-admin.multilanguage-input :label="trans('admin.name')" :is-required="false"
                                                                 field-name="title"
                                                                 :values="isset($blogPageConfig) ? $blogPageConfig->getTranslations('title') : []"/>
                                    <!-- META TITLE -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_title')" :is-required="false"
                                                                 field-name="meta_title"
                                                                 :values="isset($blogPageConfig) ? $blogPageConfig->getTranslations('meta_title') : []"/>
                                    <!-- META DESCRIPTION -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_description')"
                                                                 :is-required="false" field-name="meta_description"
                                                                 :values="isset($blogPageConfig) ? $blogPageConfig->getTranslations('meta_description') : []"/>
                                    <!-- META KEY WORDS -->
                                    <x-admin.multilanguage-input :label="trans('admin.meta_keywords')"
                                                                 :is-required="false" field-name="meta_keywords"
                                                                 :values="isset($blogPageConfig) ? $blogPageConfig->getTranslations('meta_keywords') : []"/>

                                    <div class="col-md-12 mb-5">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>{{ trans('admin.meta_tags') }}</label>
                                                <textarea type="text" name="meta_tags" class="form-control">@isset($blogPageConfig){{ $blogPageConfig['meta_tags'] }}@endisset</textarea>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12 text-right">
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

@endpush

