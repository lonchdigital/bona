@props(['articles'])

@if(count($articles) > 0)
    <section class="bona-blog" aria-labelledby="home-blog-title">
        <div class="bona-shell">
            <header class="bona-section-heading bona-section-heading--split">
                <div>
                    <p class="bona-kicker">{{ trans('base.blog_latest') }}</p>
                    <h2 id="home-blog-title">{{ trans('base.blog') }}</h2>
                </div>
                <a class="bona-text-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page') }}">
                    {{ trans('base.blog_all') }} <span aria-hidden="true">&#8594;</span>
                </a>
            </header>

            <div class="bona-blog__grid">
                @foreach($articles as $article)
                    <article class="bona-post-card">
                        <a class="bona-post-card__image" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                            @if($article->hero_image_url)
                                <img src="{{ $article->hero_image_url }}" alt="{{ $article->name }}" loading="lazy" decoding="async">
                            @endif
                            <time class="bona-post-card__date" datetime="{{ $article->created_at->toDateString() }}">
                                {{ $article->created_at->translatedFormat('d M Y') }}
                            </time>
                        </a>
                        <div class="bona-post-card__body">
                            <h3>
                                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('blog.article.page', ['blogArticleSlug' => $article->slug]) }}">
                                    {{ $article->name }}
                                </a>
                            </h3>
                            @if(filled($article->preview_text))
                                <p>{{ \Illuminate\Support\Str::limit(strip_tags($article->preview_text), 170) }}</p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>
@endif
