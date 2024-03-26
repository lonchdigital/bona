@extends('layouts.store-main')

@section('title')

    @if(isset($blogPageConfig))
        <title>{{ $blogPageConfig->meta_title }}</title>

        @if($blogPageConfig->meta_title)
            <meta name="title" content="{{ $blogPageConfig->meta_title }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        @endif

        @if($blogPageConfig->meta_description)
            <meta name="title" content="{{ $blogPageConfig->meta_description }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_description_tag }}">
        @endif

        @if($blogPageConfig->meta_keywords)
            <meta name="title" content="{{ $blogPageConfig->meta_keywords }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_keywords_tag }}">
        @endif

    @else
        <title>{{ config('app.name') . ' - ' . trans('base.blog') }}</title>
    @endif
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'blog']])

    <!-- ========================  Blog ======================== -->
    <section class="blog art-section-pd">

        <div class="container">

            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ (isset($blogPageConfig)) ? $blogPageConfig->title : trans('base.blog') }}</h1>
                    </div>
                </header>
            </div>

            <div class="row">

                @if( count($articles) > 0 )
                    <div class="art-blog-archive-wrapper">
                        @foreach($articles as $article)
                            <div class="col-lg-4">
                                <article class="art-post-archive-item">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                                        <div class="image" style="background-image:url({{ $article->hero_image_url }})">
                                            <img src="{{ $article->hero_image_url }}" alt="">
                                        </div>
                                        <div class="entry entry-post">
                                            <div class="preview-post-left">
                                                <div class="date-wrapper">
                                                    <div class="date">
                                                        <strong>{{ $article->created_at->format('d') }}</strong>
                                                        <span>{{ $article->created_at->format('M') }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="preview-post-right">
                                                <div class="title">
                                                    <h2 class="h5">{{ $article->name }}</h2>
                                                </div>
                                                <div class="art-preview-text"><p>{{ $article->preview_text }}</p></div>
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="col-12">
                        <p class="nothing-found-text mt-5">{{ trans('base.nothing_found') }}</p>
                    </div>
                @endif

            </div> <!--/row-->

            <!-- === pagination === -->
            {{ $articles->links('pagination.common') }}

        </div><!--/container-->
    </section>

@endsection
