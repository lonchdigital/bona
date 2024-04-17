@extends('layouts.admin-main')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12">
                <h2 class="page-title">{{trans('admin.services')}}</h2>


                <services-page-edit-form
                    base-language="{{ $baseLanguage }}"
                    :available-languages="{{ json_encode($availableLanguages) }}"
                    submit-route="{{ route('admin.services.edit') }}"

                    @if($config)
                        :page-meta-title="{{ json_encode($config->getTranslations('meta_title')) }}"
                        :page-meta-description="{{ json_encode($config->getTranslations('meta_description')) }}"
                        :page-meta-keywords="{{ json_encode($config->getTranslations('meta_keywords')) }}"
                        :product-meta-tags="{{ json_encode($config->meta_tags) }}"
                    @endif

                    @if(count($sections))
                        :service-sections="{{ json_encode($sections) }}"
                    @endif

                />

            </div>
        </div>
    </div>
@endsection
@section('vue')
    <vue/>
@endsection
