@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>

    @if(isset($config))
        @if($config->meta_title)
            <meta name="title" content="{{ $config->meta_title }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        @endif

        @if($config->meta_description)
            <meta name="title" content="{{ $config->meta_description }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_description_tag }}">
        @endif

        @if($config->meta_keywords)
            <meta name="title" content="{{ $config->meta_keywords }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_keywords_tag }}">
        @endif
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
                                <a href="{{ $section->button_url }}" target="_blank" class="btn btn-empty color-dark" >{{ $section->button_text }}</a>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endforeach
    </div>


@stop
