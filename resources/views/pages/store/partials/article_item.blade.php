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
                    <span class="h5">{{ $article->name }}</span>
                </div>
                <div class="art-preview-text"><p>{{ $article->preview_text }}</p></div>
            </div>
        </div>
    </a>
</article>
