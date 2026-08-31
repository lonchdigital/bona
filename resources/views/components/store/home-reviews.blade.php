@props(['testimonials'])

@if(count($testimonials) > 0)
    @php
        $googleReviewsUrl = config('organization.map_url');
    @endphp

    <section class="bona-reviews" aria-labelledby="home-reviews-title">
        <div class="bona-shell">
            <header class="bona-section-heading bona-section-heading--split">
                <div>
                    <p class="bona-kicker">Google Maps</p>
                    <h2 id="home-reviews-title">{{ trans('base.client_testimonials') }}</h2>
                </div>

                <div class="bona-section-heading__actions">
                    @if(count($testimonials) > 1)
                        <div class="bona-slider-nav" aria-label="{{ trans('base.client_testimonials') }}">
                            <button class="bona-slider-nav__button bona-reviews__nav--prev" type="button" aria-label="{{ trans('base.previous_reviews') }}">
                                <span aria-hidden="true">&#8592;</span>
                            </button>
                            <button class="bona-slider-nav__button bona-reviews__nav--next" type="button" aria-label="{{ trans('base.next_reviews') }}">
                                <span aria-hidden="true">&#8594;</span>
                            </button>
                        </div>
                    @endif

                    @if($googleReviewsUrl)
                        <a class="bona-outline-link" href="{{ $googleReviewsUrl }}" target="_blank" rel="noopener noreferrer nofollow">
                            {{ trans('base.google_reviews') }}
                        </a>
                    @endif
                </div>
            </header>

            <div class="swiper bona-reviews__slider" data-reviews-slider>
                <div class="swiper-wrapper">
                    @foreach($testimonials as $testimonial)
                        @php
                            $rating = max(0, min(5, (int) $testimonial->rating));
                        @endphp
                        <article class="swiper-slide bona-review-card">
                            <div class="bona-review-card__stars" aria-label="{{ trans_choice('base.review_rating', $rating, ['rating' => $rating]) }}">
                                <span>{{ str_repeat('★', $rating) }}</span><span class="bona-review-card__stars-muted">{{ str_repeat('★', 5 - $rating) }}</span>
                            </div>
                            <p class="bona-review-card__text">{{ $testimonial->review }}</p>
                            <div class="bona-review-card__meta">
                                @if(filled($testimonial->url))
                                    <a class="bona-review-card__author" href="{{ $testimonial->url }}" target="_blank" rel="noopener noreferrer nofollow">
                                        {{ $testimonial->name }}
                                    </a>
                                @else
                                    <span class="bona-review-card__author">{{ $testimonial->name }}</span>
                                @endif

                                @if(filled($testimonial->date))
                                    <time class="bona-review-card__date" datetime="{{ $testimonial->date }}">
                                        {{ \Illuminate\Support\Carbon::parse($testimonial->date)->format('d.m.Y') }}
                                    </time>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
