@props(['feed' => null])

<section class="bona-instagram" aria-labelledby="instagram-title">
    <header class="bona-section-heading">
        <p class="bona-kicker">@bona_doors</p>
        <h2 id="instagram-title">{{ trans('base.we_are_in_instagram') }}</h2>
    </header>

    @if(is_array($feed) && count($feed) > 0)
        <div class="bona-instagram__grid">
            @foreach($feed as $instagramItem)
                <a
                    class="bona-instagram__item"
                    href="{{ $instagramItem['permalink'] }}"
                    target="_blank"
                    rel="noopener noreferrer nofollow"
                    aria-label="{{ trans('base.we_are_in_instagram') }}: {{ \Illuminate\Support\Str::limit($instagramItem['caption'] ?: '@bona_doors', 90) }}"
                >
                    <img
                        src="{{ $instagramItem['image_url'] }}"
                        alt="{{ \Illuminate\Support\Str::limit($instagramItem['caption'] ?: 'Bona Doors в Instagram', 120) }}"
                        loading="lazy"
                        decoding="async"
                    >
                    @if(($instagramItem['media_type'] ?? null) === 'VIDEO')
                        <span class="bona-instagram__video" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 18 18" fill="none">
                                <path d="M6.75 4.8 13 9l-6.25 4.2V4.8Z" fill="currentColor"/>
                            </svg>
                        </span>
                    @endif
                </a>
            @endforeach
        </div>
    @endif

    <div class="bona-instagram__cta">
        <a
            href="https://www.instagram.com/bona_doors/"
            class="bona-instagram__button"
            target="_blank"
            rel="noopener noreferrer nofollow"
        >
            {{ trans('base.subscribe') }} на @bona_doors
        </a>
    </div>
</section>
