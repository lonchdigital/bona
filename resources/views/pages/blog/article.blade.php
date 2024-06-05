@extends('layouts.store-main')

@section('title')

    @if(isset($blogArticle))
        @if($blogArticle->meta_title)
            <title>{{ $blogArticle->meta_title }}</title>
            <meta name="title" content="{{ $blogArticle->meta_title }}">
        @else
            <title>{{ $blogArticle->name }}</title>
            <meta name="title" content="{{ $blogArticle->name }}">
        @endif

        @if($blogArticle->meta_description)
            <meta name="description" content="{{ $blogArticle->meta_description }}">
        @else
            <meta name="description" content="{{ $blogArticle->preview_text }}">
        @endif

        @if($blogArticle->meta_keywords)
            <meta name="keywords" content="{{ $blogArticle->meta_keywords }}">
        @endif

        @if($blogArticle->meta_tags)
            {!! $blogArticle->meta_tags !!}
        @endif

        <meta property="og:title" content="{{ $blogArticle->name . ' - ' . trans('base.site_title') }}">

    @endif

@endsection

@section('content')

    <!-- ========================  Main header ======================== -->

    <section class="main-header main-header-blog" style="background-image:url({{ $blogArticle->hero_image_url }})">
        <header>
                {{-- TODO:: Remove when finish --}}
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

        <script type="application/ld+json">
            {
                "@context": "https://schema.org",
                "@type": "Article",
                "mainEntityOfPage": {
                    "@type": "WebPage",
                    "@id": "{{ url('/') .'/'. $blogArticle->slug }}"
                },
                "headline": "{{ $blogArticle->name }}",
                "image": "{{ url('/') .'/storage/'. $blogArticle->hero_image_path }}",
                "publisher": {
                    "@type": "Organization",
                    "name": "Bona-Doors"
                },
                "articleBody": "<p>{{ $blogArticle->preview_text }}</p> ",
                "articleSection": "Блог",
                "datePublished": "{{ $blogArticle->created_at }}",
                "dateModified": "{{ $blogArticle->updated_at }}"
            }
        </script>

        @if(array_key_exists('authorName', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['authorName']))
            <script type="application/ld+json">
                {
                    "@context": "https://schema.org",
                    "@type": "Person",
                    "name": "{{ $applicationGlobalOptions['authorName'][app()->getLocale()] }}",
                    "alternateName": "{{ $applicationGlobalOptions['authorName'][app()->getLocale()] }}",
                    "image": "{{ '/storage/' . $applicationGlobalOptions['authorAvatar'] }}",
                    "jobTitle": "{{ $applicationGlobalOptions['authorDescription'][app()->getLocale()] }}",
                    "worksFor": {
                        "@type": "Organization",
                        "name": "{{ trans('base.organization') }}",
                    }
                }
            </script>
        @endif

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


                                <div class="art-post-author">

                                    <div class="author-avatar">
                                        @if(array_key_exists('authorAvatar', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['authorAvatar']))
                                            <img src="{{ '/storage/' . $applicationGlobalOptions['authorAvatar'] }}" alt="Author avatar">
                                        @endif
                                    </div>

                                    <div class="author-data">
                                        <span class="post-author-label">{{ trans('base.author') }}</span>
                                        @if(array_key_exists('authorName', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['authorName']))
                                            <span class="post-author-itself">{{ $applicationGlobalOptions['authorName'][app()->getLocale()] }}</span>
                                        @endif
                                        @if(array_key_exists('authorDescription', $applicationGlobalOptions) && !is_null($applicationGlobalOptions['authorDescription']))
                                            <span class="post-author-status">{{ $applicationGlobalOptions['authorDescription'][app()->getLocale()] }}</span>
                                        @endif
                                    </div>

                                </div>

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
                            @include('pages.store.partials.article_item', ['article' => $latestArticle])
                        </div>
                    @endforeach
                </div>
            </div> <!--/row-->

        </div>

    </section>



@endsection
