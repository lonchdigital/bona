@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . $blogArticle->name }}</title>
@endsection

@section('content')

    <!-- ========================  Main header ======================== -->

    <section class="main-header main-header-blog" style="background-image:url({{ $blogArticle->hero_image_url }})">
        <header>
<!--            <div class="container text-center">
                <ol class="breadcrumb breadcrumb-inverted">
                    <li><a href="index.html"><span class="icon icon-home"></span></a></li>
                    <li><a href="blog-grid.html">Blog Category</a></li>
                    <li><a class="active" href="article.html">Decorating When You're...</a></li>
                </ol>
            </div>-->
        </header>
    </section>



    <section class="blog art-single-blog">

        <!-- ========================  Blog post ======================== -->

        <div class="container">
            <div class="row">
                <div class="col-lg-10 col-md-offset-1">
                    <div class="blog-post">

                        <div class="blog-post-content">

                            <!-- === blog post title === -->
                            <div class="blog-post-title">
                                <h1 class="blog-title">
                                    {{ $blogArticle->name }}
                                </h1>
                            </div>

                            <!-- === blog post text === -->
                            <div class="blog-post-text">

                                @foreach($blogArticle->blocks as $block)
                                    @if ($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_TEXT)

                                        {!! isset($block->content[app()->getLocale()]) ? $block->content[app()->getLocale()] : '' !!}

                                    @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_IMAGE)
                                        <div class="mx-auto">
                                            @foreach( $block->content['images'] as $image )
                                                <img src="{{ $image['image_url'] }}" alt="">
                                            @endforeach
                                        </div>
                                    @elseif($block->type_id === \App\DataClasses\BlogArticleBlockTypesDataClass::TYPE_VIDEO)
                                        <div class="mx-auto blog-video-wrapper">
                                            <div class="plyr__video-embed js-player">
                                                <iframe src="{{ $block->content['video_link'] }}" allowfullscreen allowtransparency allow="autoplay"></iframe>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

<!--                                <div class="art-post-author">
                                    <span class="post-author-label">{{ trans('base.author') }}</span>
                                    <span class="post-author-itself">{{ $blogArticle->creator->first_name . ' ' . $blogArticle->creator->last_name }}</span>
                                    <span class="post-author-status">{{ trans('base.door_expert') }}</span>
                                </div>-->

                            </div>


                        </div>


                    </div><!--blog-post-->
                </div><!--col-sm-8-->
            </div> <!--/row-->
        </div><!--/container-->
    </section>

    <section class="blog art-single-latest-articles">

        <div class="container">

            <div class="row">
                <header class="col-12 art-header-left">
                    <div>
                        <h2 class="title">{{trans('base.article_read_also')}}</h2>
                    </div>
                </header>
            </div>

            <div class="row">
                <div class="art-blog-archive-wrapper">
                    @foreach($latestArticles as $latestArticle)
                        <div class="col-lg-4">
                            <article class="art-post-archive-item">
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $latestArticle->slug]) }}">
                                    <div class="image" style="background-image:url({{ $latestArticle->hero_image_url }})">
                                        <img src="{{ $latestArticle->hero_image_url }}" alt="">
                                    </div>
                                    <div class="entry entry-post">
                                        <div class="preview-post-left">
                                            <div class="date-wrapper">
                                                <div class="date">
                                                    <strong>{{ $latestArticle->created_at->format('d') }}</strong>
                                                    <span>{{ $latestArticle->created_at->format('M') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="preview-post-right">
                                            <div class="title">
                                                <h2 class="h5">{{ $latestArticle->name }}</h2>
                                            </div>
                                            <div class="art-preview-text"><p>{{ $latestArticle->preview_text }}</p></div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div> <!--/row-->

        </div>

    </section>



@endsection
