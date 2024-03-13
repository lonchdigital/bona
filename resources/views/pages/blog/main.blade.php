@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.blog') }}</title>
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'blog']])

    <!-- ========================  Blog ======================== -->
    <section class="blog art-section-pd">

        <div class="container">

            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h2 class="title">{{trans('base.blog')}}</h2>
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
