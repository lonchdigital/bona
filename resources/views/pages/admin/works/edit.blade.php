@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                @if(isset($work))
                    <h2 class="page-title">{{ trans('admin.work_edit') }}</h2>
                @else
                    <h2 class="page-title">{{ trans('admin.work_new') }}</h2>
                @endisset


                <work-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ isset($work) ?  route('admin.work.edit', ['work' => $work]) : route('admin.work.create') }}"

                    @if(isset($work))
                    :work-name="{{ json_encode($work->getTranslations('name')) }}"
                    :work-slug="{{ json_encode($work['slug']) }}"
                    :work-meta-title="{{ json_encode($work->getTranslations('meta_title')) }}"
                    :work-meta-description="{{ json_encode($work->getTranslations('meta_description')) }}"
                    :work-meta-keywords="{{ json_encode($work->getTranslations('meta_keywords')) }}"
                    @endif

                    @if(isset($work))
                    :work-image="{{ json_encode($work->image_url) }}"
                    @endif

                    {{--end--}}
                />

            </div>
        </div>
    </div>
@endsection

@section('vue')
    <vue/>
@endsection

