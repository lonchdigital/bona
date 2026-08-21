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
                    :work-intro="{{ json_encode($work->getTranslations('intro')) }}"
                    :work-description="{{ json_encode($work->getTranslations('description')) }}"
                    :work-client-quote="{{ json_encode($work->getTranslations('client_quote')) }}"
                    :work-location="{{ json_encode($work->location) }}"
                    :work-doors-count="{{ json_encode($work->doors_count) }}"
                    :work-duration="{{ json_encode($work->duration) }}"
                    :work-client-name="{{ json_encode($work->client_name) }}"
                    :work-is-published="{{ json_encode((bool) $work->is_published) }}"
                    :work-service-title="{{ json_encode($work->getTranslations('service_title')) }}"
                    :work-service-description="{{ json_encode($work->getTranslations('service_description')) }}"
                    :work-price-note="{{ json_encode($work->getTranslations('price_note')) }}"
                    :work-price-from="{{ json_encode($work->price_from) }}"
                    :work-price-currency="{{ json_encode($work->price_currency ?: 'UAH') }}"
                    :work-images="{{ json_encode($work->images->map(fn ($image) => [
                        'id' => $image->id,
                        'caption' => $image->getTranslations('caption'),
                        'image_url' => $image->image_url,
                    ])) }}"
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

