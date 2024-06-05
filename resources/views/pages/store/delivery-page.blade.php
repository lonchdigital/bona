@extends('layouts.store-main')

@section('title')

    @if(isset($deliveryConfig))
        @if($deliveryConfig->meta_title)
            <title>{{ $deliveryConfig->meta_title }}</title>
            <meta name="title" content="{{ $deliveryConfig->meta_title }}">
        @endif

        @if($deliveryConfig->meta_description)
            <meta name="description" content="{{ $deliveryConfig->meta_description }}">
        @endif
        @if($deliveryConfig->meta_keywords)
            <meta name="keywords" content="{{ $deliveryConfig->meta_keywords }}">
        @endif

        @if($deliveryConfig->meta_tags)
            {!! $deliveryConfig->meta_tags !!}
        @endif
    @endif

    <meta property="og:title" content="{{ trans('base.delivery') . ' - ' . trans('base.site_title') }}">

@endsection


@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'delivery']])

    <div class="art-section-pd">
        <div class="container">
            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ trans('base.delivery') }}</h1>
                    </div>
                </header>
            </div>
        </div>
    </div>

    <div class="common-page-section-wrapper">
        <section class="art-common-page-section">
            <div class="container">
                <div class="art-row-block art-odd">
                    @if( !empty($deliveryConfig->iframe) )
                        <div class="col-md-5 video-side">{!! $deliveryConfig->iframe !!}</div>
                    @else
                        <div class="col-md-5 image-side">
                            <img src="{{ $deliveryConfig->imageUrl }}" alt="block image">
                        </div>
                    @endif
                    <div class="col-md-7 desc-side">
                        <div class="h5 title">{{ $deliveryConfig->title }}</div>
                        {!! $deliveryConfig->description !!}
                        @if( !empty($deliveryConfig->button_url) )
                            <a href="{{ $deliveryConfig->button_url }}" target="_blank" class="btn btn-empty color-dark" >{{ $deliveryConfig->button_text }}</a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>

@stop
