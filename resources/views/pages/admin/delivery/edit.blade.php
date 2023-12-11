@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">Delivery</h2>


                <common-section-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.delivery.edit') }}"

                    @if( !is_null($deliveryConfig) )
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
