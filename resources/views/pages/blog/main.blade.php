@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.blog') }}</title>
@endsection

@section('content')
    <main class="main blog-p">
        <div class="content">
            <section class="blog-banner-top mb-10 mb-md-12">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="head text-lg-center mb-3 mt-6 mt-md-8">{{ trans('base.blog') }} {{ config('app.name') }}</div>
                            <div class="row">
                                <div class="col col-lg-6 mx-auto mb-8">
                                    <div class="subhead text-lg-center">{{ trans('base.blog_head_text') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container container-max">
                    <div class="row">
                        <div class="col">
                            <div class="swiper-blog-banner-top">
                                <div class="swiper-wrapper">
                                    @foreach($slides as $slide)
                                        <div class="swiper-slide">
                                            <div class="bg-wrap row no-gutters">
                                                <div class="col">
                                                    <img src="{{ $slide->slide_image_1_url }}" alt="img">
                                                </div>
                                                <div class="col">
                                                    <img src="{{ $slide->slide_image_2_url }}" alt="img">
                                                </div>
                                            </div>
                                            <div class="content">
                                                <div class="title">{{ $slide->collection->name }}</div>
                                                <div class="subtitle">{{ $slide->description }}</div>
                                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.collection.page', ['collectionSlug' => $slide->collection->slug]) }}" class="btn btn-black-custom">{{ trans('base.see_details') }}</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="swiper-control mt-6">
                                <div class="swiper-pagination"></div>
                                <div class="swiper-buttons ml-auto d-none d-md-flex">
                                    <div class="button-slider-prev">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                        </svg>
                                    </div>
                                    <div class="button-slider-next">
                                        <svg>
                                            <use xlink:href="{{ Vite::asset('resources/img/icon.svg') }}#i-arrow-circle"></use>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <section class="blog mb-14" id="blog">
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="nav-blog-category mb-0 mb-md-3">
                                <div class="swiper-wrapper justify-content-xxl-center">
                                    <div class="swiper-slide @if(!isset($selectedCategory)) active @endif"><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">{{ trans('base.see_all_articles') }}</a></div>
                                    @foreach($categories as $category)
                                        <div class="swiper-slide @if(isset($selectedCategory) && $selectedCategory->id === $category->id) active @endif">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.articles-by-category.page', ['blogCategorySlug' => $category->slug]) }}">{{ $category->name }}</a>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="swiper-scrollbar d-none"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="container-fluid mb-3 mb-md-14 px-0">
                    <hr>
                </div>
                <div class="container">
                    <div class="row">
                        <div class="col">
                            <div class="row flex-column flex-md-row">
                                @foreach($articles as $article)
                                    <div class="col col-md-6 mb-10 mb-lg-17 content">
                                        <div class="card h-100 border-0">
                                            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                                                <img class="card-img-top" src="{{ $article->hero_image_url }}" alt="img">
                                            </a>
                                            <div class="card-body mt-5 p-0">
                                                <div class="mb-2">
                                                    <a href="#">
                                                        <div class="card-title article-title">{{ $article->name }}</div>
                                                    </a>
                                                </div>
                                                <div class="d-flex align-items-center">
                                                    <div class="article-date mr-3">{{ $article->created_at->format('d.m.Y') }}</div>
                                                    <div class="article-author">{{ trans('base.team') }} {{ config('app.name') }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    {{ $articles->links('pagination.blog') }}
                </div>
            </section>
            @guest
                @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                    <x-email-subscription-form/>
                @endif
            @endguest
        </div>
    </main>
@endsection
