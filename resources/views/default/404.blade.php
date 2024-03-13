@extends('layouts.store-main')

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => 404]])

    <div class="common-page-section-wrapper">
        <section class="art-common-page-section">
            <div class="container">
                <div class="art-row-block">

                    <div class="col-md-5 left-side desc-side art-404">
                        <div class="left-side-content">
                            <h1 class="title">404</h1>
                            <p>Сторінку не знайдено</p>
                            <a href="{{ route('store.home') }}" class="btn btn-empty color-dark">{{ trans('base.go_to_main') }}</a>
                        </div>
                    </div>
                    <div class="col-md-7 right-side">
                        <img class="main-logo-admin" src="{{ asset('storage/bg-images/404.png') }}" alt="404">
                    </div>

                </div>
            </div>
        </section>
    </div>

@stop
