@extends('layouts.store-main')

@section('title')

    @if(isset($blogPageConfig))
        @if($blogPageConfig->meta_title)
            <title>{{ $blogPageConfig->meta_title }}</title>
            <meta name="title" content="{{ $blogPageConfig->meta_title }}">
        @endif

        @if($blogPageConfig->meta_description)
            <meta name="description" content="{{ $blogPageConfig->meta_description }}">
        @endif
        @if($blogPageConfig->meta_keywords)
            <meta name="keywords" content="{{ $blogPageConfig->meta_keywords }}">
        @endif

        @if($blogPageConfig->meta_tags)
            {!! $blogPageConfig->meta_tags !!}
        @endif
    @endif

    <meta property="og:title" content="{{ trans('base.blog') . ' - ' . trans('base.site_title') }}">

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
                                @include('pages.store.partials.article_item', ['article' => $article])
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
