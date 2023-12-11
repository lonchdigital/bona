@extends('layouts.store-main')

@section('title')
    <title>{{ config('app.name') . ' - ' . trans('base.blog') }}</title>
@endsection

@section('content')

    <x-header-component :data="[
        '#' => 'blog'
    ]" />


    <!-- ========================  Blog ======================== -->

    <section class="blog art-section-pd">

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

            <!-- === pagination === -->
            {{ $articles->links('pagination.common') }}

        </div><!--/container-->
    </section>


    <main class="main blog-p">
        <div class="content">
            @guest
                @if(!\Illuminate\Support\Facades\Session::exists('email_subscription_sent'))
                    <x-email-subscription-form/>
                @endif
            @endguest
        </div>
    </main>
@endsection
