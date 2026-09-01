@extends('layouts.store-main')

@section('title')

    @if(isset($aboutUsConfig))
        @if($aboutUsConfig->meta_title)
            <title>{{ $aboutUsConfig->meta_title }}</title>
            <meta name="title" content="{{ $aboutUsConfig->meta_title }}">
        @endif

        @if($aboutUsConfig->meta_description)
            <meta name="description" content="{{ $aboutUsConfig->meta_description }}">
        @endif
        @if($aboutUsConfig->meta_keywords)
            <meta name="keywords" content="{{ $aboutUsConfig->meta_keywords }}">
        @endif

        @if($aboutUsConfig->meta_tags)
            {!! $aboutUsConfig->meta_tags !!}
        @endif

        <meta property="og:title" content="{{ trans('base.about_us') . ' - ' . trans('base.site_title') }}">

        @if($aboutUsConfig->meta_description)
            <meta property="og:description" content="{{ $aboutUsConfig->meta_description }}">
        @endif

        <meta name="twitter:card" content="summary_large_image">
    @endif

@endsection

@if(isset($aboutUsConfig) && $aboutUsConfig->image)
    @section('og_image', App\Helpers\PreviewImage::url($aboutUsConfig->image))
@endif

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

    @if($aboutUsFacts->count())
        <section class="art-section-pd art-about-facts">
            <div class="container">
                @if($aboutUsConfig->facts_title)
                    <h2 class="title h2">{{ $aboutUsConfig->facts_title }}</h2>
                @endif
                <ul class="art-about-facts__list">
                    @foreach($aboutUsFacts as $fact)
                        <li>
                            <span class="art-about-facts__value">{{ $fact->value }}</span>
                            @if($fact->label)
                                <span class="art-about-facts__label">{{ $fact->label }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if($aboutUsConfig->history_text)
        <section class="art-section-pd art-about-history">
            <div class="container">
                @if($aboutUsConfig->history_title)
                    <h2 class="title h2">{{ $aboutUsConfig->history_title }}</h2>
                @endif
                <div class="art-about-history__text">{!! $aboutUsConfig->history_text !!}</div>
            </div>
        </section>
    @endif

    @if($aboutUsSteps->count())
        <section class="art-section-pd art-about-steps">
            <div class="container">
                @if($aboutUsConfig->steps_title)
                    <h2 class="title h2">{{ $aboutUsConfig->steps_title }}</h2>
                @endif
                <ol class="art-about-steps__list">
                    @foreach($aboutUsSteps as $step)
                        <li>
                            <h3>{{ $step->title }}</h3>
                            @if($step->text)
                                <p>{{ $step->text }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </section>
    @endif

    @if($aboutUsTeam->count())
        <section class="art-section-pd art-about-team">
            <div class="container">
                @if($aboutUsConfig->team_title)
                    <h2 class="title h2">{{ $aboutUsConfig->team_title }}</h2>
                @endif
                <ul class="art-about-team__list">
                    @foreach($aboutUsTeam as $member)
                        <li>
                            @if($member->photo_url)
                                <img src="{{ $member->photo_url }}" alt="{{ $member->name }}{{ $member->role ? ', ' . $member->role : '' }}" loading="lazy">
                            @endif
                            <span class="art-about-team__name">{{ $member->name }}</span>
                            @if($member->role)
                                <span class="art-about-team__role">{{ $member->role }}</span>
                            @endif
                            @if($member->experience)
                                <span class="art-about-team__experience">{{ $member->experience }}</span>
                            @endif
                            @if($member->quote)
                                <p class="art-about-team__quote">{{ $member->quote }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </section>
    @endif

    @if($aboutUsConfig->cta_title || $aboutUsConfig->cta_text)
        <section class="art-section-pd art-about-cta">
            <div class="container text-center">
                @if($aboutUsConfig->cta_title)
                    <h2 class="title h2">{{ $aboutUsConfig->cta_title }}</h2>
                @endif
                @if($aboutUsConfig->cta_text)
                    <p>{{ $aboutUsConfig->cta_text }}</p>
                @endif
                @if($aboutUsConfig->cta_button_text)
                    <a href="{{ $aboutUsConfig->cta_button_url ?: '#dialog-call-measurer' }}"
                       class="btn btn-main"
                       @if(!$aboutUsConfig->cta_button_url) data-lead-modal-open="dialog-call-measurer" @endif>
                        {{ $aboutUsConfig->cta_button_text }}
                    </a>
                @endif
            </div>
        </section>
    @endif

    <section class="art-brands-list">
        <div class="container">

            <div class="swiper art-brands-owl-items mt-6">
                <div class="swiper-wrapper">
                    @foreach( $brands as $brand )
                        <div class="swiper-slide">
                            @include('pages.store.partials.brand_item', ['brand' => $brand])
                        </div>
                    @endforeach
                </div>
                <div class="swiper-pagination"></div>
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
                                        <img src="{{ $article->hero_image_url }}" alt="{{ $article->name }}">
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
