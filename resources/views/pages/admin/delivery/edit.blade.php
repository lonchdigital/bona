@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{trans('admin.delivery')}}</h2>


                <common-section-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.delivery.edit') }}"

                    @if( !is_null($deliveryConfig) )
                        :page-meta-title="{{ json_encode($deliveryConfig->getTranslations('meta_title')) }}"
                        :page-meta-description="{{ json_encode($deliveryConfig->getTranslations('meta_description')) }}"
                        :page-meta-keywords="{{ json_encode($deliveryConfig->getTranslations('meta_keywords')) }}"
                        :page-meta-tags="{{ json_encode($deliveryConfig->meta_tags) }}"

                        :title="{{ json_encode($deliveryConfig->getTranslations('title')) }}"
                        :description="{{ json_encode($deliveryConfig->getTranslations('description')) }}"
                        :button-text="{{ json_encode($deliveryConfig->getTranslations('button_text')) }}"
                        :button-url="{{ json_encode($deliveryConfig->button_url) }}"

                        @if(!empty($deliveryConfig->image))
                            image-url="{{ $deliveryConfig->image_url }}"
                        @endif

                        :video-iframe="{{ json_encode($deliveryConfig->iframe) }}"
                    @endif
                />

            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
