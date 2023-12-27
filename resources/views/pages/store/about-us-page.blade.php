@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - HOME' }}</title>
@endsection

@section('content')

    <x-header-component :data="[
        '#' => 'about_us'
    ]" />

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
                    <div class="item">
                        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-by-brand.page', ['brand' => $brand]) }}">
                            @if(!is_null($brand->logo_image_path))
                                <img src="{{$brand->logo_image_url}}" alt="Brand logo">
                            @else
                                {{ $brand->name }}
                            @endif
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ========================  Blog ======================== -->
    <section class="blog art-dark-bg">
        <div class="container">

            <header class="art-header-left">
                <div>
                    <h2 class="title">{{trans('base.blog')}}</h2>
                </div>
            </header>

            <div class="row">
                @foreach($articles as $article)
                    <div class="col-sm-4">
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
            </div> <!--/row-->
        </div><!--/container-->
    </section>

@stop
