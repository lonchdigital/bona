<section class="bona-article-share" aria-labelledby="article-share-title">
    <div>
        <p>{{ trans('base.article_share_kicker') }}</p>
        <h2 id="article-share-title">{{ trans('base.article_share') }}</h2>
    </div>
    <div class="bona-article-share__actions">
        <a class="bona-article-share__action" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($articleUrl) }}" target="_blank" rel="noopener nofollow">
            <span class="bona-article-share__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="M13.7 21v-8h2.8l.4-3.1h-3.2v-2c0-.9.3-1.5 1.6-1.5H17V3.6c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.1H7.5V13h2.8v8h3.4Z"/></svg>
            </span>
            <span>Facebook</span>
        </a>
        <a class="bona-article-share__action" href="https://t.me/share/url?url={{ urlencode($articleUrl) }}&text={{ urlencode($blogArticle->name) }}" target="_blank" rel="noopener nofollow">
            <span class="bona-article-share__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="currentColor"><path d="m21.4 4.2-3 14.2c-.2 1-.8 1.2-1.6.7l-4.6-3.4-2.2 2.1c-.2.3-.5.5-.9.5l.3-4.7 8.6-7.8c.4-.3-.1-.5-.6-.2L6.8 12.3l-4.6-1.4c-1-.3-1-1 .2-1.5l17.8-6.9c.8-.3 1.5.2 1.2 1.7Z"/></svg>
            </span>
            <span>Telegram</span>
        </a>
        <button type="button" class="bona-article-share__action js-article-share-copy" data-url="{{ $articleUrl }}" data-copied-text="{{ trans('base.article_link_copied') }}">
            <span class="bona-article-share__icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg>
            </span>
            <span data-share-copy-label aria-live="polite">{{ trans('base.article_copy_link') }}</span>
        </button>
    </div>
</section>
