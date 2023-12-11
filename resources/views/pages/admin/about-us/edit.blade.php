@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">About us</h2>

                <common-section-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.about-us.edit') }}"

                    @if( !is_null($aboutUsConfig) )
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
