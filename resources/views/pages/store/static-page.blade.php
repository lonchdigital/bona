@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $heading }}</title>

    @if(isset($allData))
        @if($allData['meta_title'])
            <meta name="title" content="{{ $allData['meta_title'] }}">
        @endif
        @if($allData['meta_description'])
            <meta name="title" content="{{ $allData['meta_description'] }}">
        @endif
        @if($allData['meta_keywords'])
            <meta name="title" content="{{ $allData['meta_keywords'] }}">
        @endif
    @endif
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => $heading]])

    <section class="art-common-page-section">
        <div class="container">
            <h1>{{ $heading }}</h1>
            {!! $allData['content'] !!}
        </div>
    </section>

@endsection
