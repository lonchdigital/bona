@if(! empty($articleRecommendedLinks) || ! empty($articleUsefulLinks))
    <div class="bona-article-links" aria-label="{{ trans('base.article_navigation_resources') }}">
        @if(! empty($articleRecommendedLinks))
            <section class="bona-article-links__group bona-article-links__group--recommended">
                <p>{{ trans('base.article_recommended_kicker') }}</p>
                <h2>{{ trans('base.article_recommended_links') }}</h2>
                <ul>
                    @foreach($articleRecommendedLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}">
                                <span>{{ $link['title'] }}</span>
                                <span aria-hidden="true">→</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif

        @if(! empty($articleUsefulLinks))
            <section class="bona-article-links__group bona-article-links__group--useful">
                <p>{{ trans('base.article_useful_kicker') }}</p>
                <h2>{{ trans('base.article_useful_links') }}</h2>
                <ul>
                    @foreach($articleUsefulLinks as $link)
                        @php($isExternalArticleLink = Illuminate\Support\Str::startsWith($link['url'], ['http://', 'https://']) && ! Illuminate\Support\Str::startsWith($link['url'], url('/')))
                        <li>
                            <a href="{{ $link['url'] }}" @if($isExternalArticleLink) target="_blank" rel="noopener" @endif>
                                <span>{{ $link['title'] }}</span>
                                <span aria-hidden="true">{{ $isExternalArticleLink ? '↗' : '→' }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </section>
        @endif
    </div>
@endif
