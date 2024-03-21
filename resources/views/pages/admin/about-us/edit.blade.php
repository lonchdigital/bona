@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{trans('base.about_us')}}</h2>

                <common-section-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.about-us.edit') }}"

                    @if( !is_null($aboutUsConfig) )
                        :page-meta-title="{{ json_encode($aboutUsConfig->getTranslations('meta_title')) }}"
                        :page-meta-description="{{ json_encode($aboutUsConfig->getTranslations('meta_description')) }}"
                        :page-meta-keywords="{{ json_encode($aboutUsConfig->getTranslations('meta_keywords')) }}"

                        :title="{{ json_encode($aboutUsConfig->getTranslations('title')) }}"
                        :description="{{ json_encode($aboutUsConfig->getTranslations('description')) }}"
                        :button-text="{{ json_encode($aboutUsConfig->getTranslations('button_text')) }}"
                        :button-url="{{ json_encode($aboutUsConfig->button_url) }}"

                        @if(!empty($aboutUsConfig->image))
                            image-url="{{ $aboutUsConfig->image_url }}"
                        @endif

                        :video-iframe="{{ json_encode($aboutUsConfig->iframe) }}"
                    @endif
                />

            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
