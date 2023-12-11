@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $heading }}</title>
@endsection

@section('content')

    <!-- ======================== Page header ======================== -->
    <section class="main-header" style="background-image:url({{ asset('storage/bg-images/header-bg.png') }})"></section>
    <header class="art-page-header">
        <div class="container">
            <ol class="breadcrumb breadcrumb-inverted font-two">
                <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}"><span class="icon icon-home"></span></a></li>
                <li><a class="active" href="#">{{ $heading }}</a></li>
            </ol>
        </div>
    </header>

    <section class="art-common-page-section">
        <div class="container">
            <h1>{{ $heading }}</h1>
            {!! $content !!}
        </div>
    </section>

@endsection
