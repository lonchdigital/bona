@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>

    @if(isset($aboutUsConfig))
        @if($aboutUsConfig->meta_title)
            <meta name="title" content="{{ $aboutUsConfig->meta_title }}">
        @elseif(isset($seogenData))
            <meta name="title" content="{{ $seogenData->meta_title_tag }}">
        @endif

        @if($aboutUsConfig->meta_description)
            <meta name="description" content="{{ $aboutUsConfig->meta_description }}">
        @elseif(isset($seogenData))
            <meta name="description" content="{{ $seogenData->meta_description_tag }}">
        @endif

        @if($aboutUsConfig->meta_keywords)
            <meta name="keywords" content="{{ $aboutUsConfig->meta_keywords }}">
        @elseif(isset($seogenData))
            <meta name="keywords" content="{{ $seogenData->meta_keywords_tag }}">
        @endif
    @endif
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['#' => 'about_us']])

    <div class="art-section-pd">
        <div class="container">
            <div class="row">
                <header class=" col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ trans('base.about_us') }}</h1>
                    </div>
                </header>
            </div>
        </div>
    </div>

    <section class="art-common-page-section">
        <div class="container">
            <div class="art-row-block art-even">
{{--                @dd($aboutUsConfig)--}}
                @if( !empty($aboutUsConfig->iframe) )
                    <div class="col-md-5 video-side">{!! $aboutUsConfig->iframe !!}</div>
                @else
                    <div class="col-md-5 image-side">
                        <img src="{{ $aboutUsConfig->imageUrl }}" alt="block image">
                    </div>
                @endif
                <div class="col-md-7 desc-side">
                    <div class="h5 title">{{ $aboutUsConfig->title }}</div>
                    {!! $aboutUsConfig->description !!}
                    @if( !empty($aboutUsConfig->button_url) )
                        <a href="{{ $aboutUsConfig->button_url }}" target="_blank" class="btn btn-empty color-dark" >{{ $aboutUsConfig->button_text }}</a>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="art-brands-list">
        <div class="container">
            <div class="art-brands-owl-items">
                @foreach( $brands as $brand )
                    @include('pages.store.partials.brand_item', ['brand' => $brand])
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========================  Blog ======================== -->
    <section class="blog art-dark-bg">
        <div class="container">

            <div class="row">
                <header class="col-12 art-header-left">
                    <div>
                        <h2 class="title">{{trans('base.blog')}}</h2>
                    </div>
                </header>
            </div>

            <div class="row">
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
            </div> <!--/row-->
        </div><!--/container-->
    </section>

@stop
