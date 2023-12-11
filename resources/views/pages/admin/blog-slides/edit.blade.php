@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{ trans('admin.blog_slides_edit') }}</h2>
                <blog-slides-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.blog-slide.edit') }}"
                    :init-data="{{ json_encode($slides) }}"
                />
            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection

