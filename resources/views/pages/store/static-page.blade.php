@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $heading }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => $heading]])

    <section class="art-common-page-section">
        <div class="container">
            <h1>{{ $heading }}</h1>
            {!! $content !!}
        </div>
    </section>

@endsection
