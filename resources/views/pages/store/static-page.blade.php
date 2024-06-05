@extends('layouts.store-main')

@section('title')

    @if(isset($allData))
        @if($allData['meta_title'])
            <title>{{ $allData['meta_title'] }}</title>
            <meta name="title" content="{{ $allData['meta_title'] }}">
        @endif

        @if($allData['meta_description'])
            <meta name="description" content="{{ $allData['meta_description'] }}">
        @endif
        @if($allData['meta_keywords'])
            <meta name="keywords" content="{{ $allData['meta_keywords'] }}">
        @endif

        @if($allData['meta_tags'])
            {!! $allData['meta_tags'] !!}
        @endif
    @endif

    <meta property="og:title" content="{{ $heading . ' - ' . trans('base.site_title') }}">

@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => $heading]])

    <section class="art-common-page-section">
        <div class="container">

            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ $heading }}</h1>
                    </div>
                </header>
            </div>

            <div class="common-page-section-wrapper">
                <section class="art-common-page-section">
                    {!! $allData['content'] !!}
                </section>
            </div>

        </div>
    </section>

@endsection
