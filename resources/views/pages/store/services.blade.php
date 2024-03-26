@extends('layouts.store-main')

@section('title')

    @if(isset($config))

        <title>{{ $config->meta_title }}</title>

        @if($config->meta_title)
            <meta name="title" content="{{ $config->meta_title }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        @endif

        @if($config->meta_description)
            <meta name="description" content="{{ $config->meta_description }}">
        @elseif(isset($seogenData))
            <meta name="description" content="{{ $seogenData->meta_description_tag }}">
        @endif

        @if($config->meta_keywords)
            <meta name="keywords" content="{{ $config->meta_keywords }}">
        @elseif(isset($seogenData))
            <meta name="keywords" content="{{ $seogenData->meta_keywords_tag }}">
        @endif

    @else
        <title>{{ config('app.name') }}</title>
    @endif

@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'services']])

    <div class="art-section-pd">
        <div class="container">
            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ trans('base.services') }}</h1>
                    </div>
                </header>
            </div>
        </div>
    </div>

    <div class="common-page-section-wrapper">
        @foreach( $sections as $section )
            <section class="art-common-page-section">
                <div class="container">
                    <div class="art-row-block{{ $loop->iteration % 2 == 0 ? ' art-even' : ' art-odd' }}">
                        <div class="col-md-5 image-side">
                            <img src="{{ $section->section_image_url }}" alt="block image">
                        </div>
                        <div class="col-md-7 desc-side">
                            <div class="h5 title">{{ $section->title }}</div>
                            {!! $section->description !!}
                            @if( !empty($section->button_url) )
                                <a href="{{ $section->button_url }}" target="_blank" class="btn btn-empty color-dark" data-fancybox data-src="#dialog-call-{{ $loop->index }}">{{ $section->button_text }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>


            <div id="dialog-call-{{ $loop->index }}" class="art-popup-call-measurer">
                <div class="art-measurer-form-wrapper">
                    <div class="container">

                        <header class="art-light">
                            <div class="text-center">
                                <h2 class="title h2">{{ $section->button_text }}</h2>
                                <div class="subtitle font-two">
                                    <p class="art-form-description">{{ trans('base.call_measurer_description') }}</p>
                                </div>
                            </div>
                        </header>

                        <div class="row">
                            <div class="col-12 text-center">
                                <form action="#" id="user-call-dialog-{{ $loop->index }}" method="post" class="art-contact-form">
                                    @csrf
                                    <div class="art-fields-row">
                                        <div>
                                            <input type="text" class="art-light-field name-field" name="name" placeholder="{{ trans('base.name') }}">
                                        </div>
                                        <div>
                                            <input type="text" class="art-light-field phone-field" name="phone" placeholder="{{ trans('base.phone') }}">
                                        </div>
                                    </div>
                                    <div class="checkbox checkbox-white agreement-line agree-field">
                                        <input type="checkbox" name="agree" value="1">
                                        <label for="fieldName">{{ trans('base.agreement_line_start') . ' ' . trans('base.agreement_line_end') }}</label>
                                    </div>
                                    <p><button type="submit" class="btn btn-empty">{{ trans('base.send') }}</button></p>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @endforeach
    </div>


@stop
