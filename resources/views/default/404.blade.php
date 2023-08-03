@extends('layouts.store-main')

@section('content')
    <main class="main">
        <div class="content">
            <section class="not-found position-relative">
                <div class="container">
                    <div class="row">
                        <div class="col-1 d-none d-lg-block"></div>
                        <div class="col col-lg-auto text-center text-lg-left">
                            <div class="not-found-content mx-auto mx-lg-0">
                                <div class="head mb-5">{{ trans('base.page_is_not_exists') }}</div>
                                <a href="{{ route('store.home') }}" class="btn-back mx-auto mb-20 mr-lg-0 ml-lg-0">
                                    <span class="mr-2">{{ trans('base.wish_list_go_to_main') }}</span>
                                    <svg>
                                        <use xlink:href="{{ asset('/static/img/icon.svg#i-arrow') }}"></use>
                                    </svg>
                                </a>
                            </div>
                            <ul class="bottom-socials list-inline mb-0">
                                <li class="list-inline-item">
                                    <a href="tel:+" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-instagram"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="##" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-twitter"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="##" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-facebook"></use>
                                        </svg>
                                    </a>
                                </li>
                                <li class="list-inline-item">
                                    <a href="##" class="link-soc" target="_blank">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-pinterest"></use>
                                        </svg>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="w-100 d-lg-none"></div>
                        <div class="col position-relative mt-4 mt-lg-0">
                            <img class="not-found-img d-none d-md-block" src="{{ Vite::asset('resources/img/404.png') }}" alt="Not-found">
                            <img class="not-found-img d-md-none" src="{{ Vite::asset('resources/img/404-m.png') }}" alt="Not-found">
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main>
@endsection

@section('noFooter')
    <div></div>
@endsection
