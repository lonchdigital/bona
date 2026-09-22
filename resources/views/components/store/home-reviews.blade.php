@props(['testimonials', 'section' => []])

@php
    $localized = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($text) => filled($text)) ?? ''));
    };
    $googleReviewsUrl = trim((string) ($section['link_url'] ?? ''));
    $sectionTitle = $localized($section['title'] ?? []) ?: trans('base.client_testimonials');
@endphp

@if($section['enabled'] ?? true)
    <section class="bona-reviews" aria-labelledby="home-reviews-title">
        <div class="bona-shell">
            <header class="bona-section-heading bona-section-heading--split">
                <div>
                    <p class="bona-kicker">{{ $localized($section['kicker'] ?? []) }}</p>
                    <h2 id="home-reviews-title">{{ $sectionTitle }}</h2>
                </div>

                <div class="bona-section-heading__actions">
                    @if(count($testimonials) > 1)
                        <div class="bona-slider-nav" aria-label="{{ $sectionTitle }}">
                            <button class="bona-slider-nav__button bona-reviews__nav--prev" type="button" aria-label="{{ trans('base.previous_reviews') }}">
                                <span aria-hidden="true"><svg viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M22.5 6h-21M6.5 1l-5 5 5 5"></path></svg></span>
                            </button>
                            <button class="bona-slider-nav__button bona-reviews__nav--next" type="button" aria-label="{{ trans('base.next_reviews') }}">
                                <span aria-hidden="true"><svg viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M1.5 6h21M17.5 1l5 5-5 5"></path></svg></span>
                            </button>
                        </div>
                    @endif

                    @if($googleReviewsUrl && $localized($section['link_label'] ?? []))
                        <a class="bona-outline-link" href="{{ $googleReviewsUrl }}" target="_blank" rel="noopener noreferrer nofollow">
                            {{ $localized($section['link_label']) }}
                        </a>
                    @endif
                </div>
            </header>

            @if(count($testimonials) > 0)
                <div class="swiper bona-reviews__slider" data-reviews-slider>
                    <div class="swiper-wrapper">
                        @foreach($testimonials as $testimonial)
                        @php
                            $rating = max(0, min(5, (int) $testimonial->rating));
                            $isGoogleReview = ($testimonial->source ?? null) === \App\Models\CustomerReview::SOURCE_GOOGLE;
                        @endphp
                        <article class="swiper-slide bona-review-card">
                            <div class="bona-review-card__stars" aria-label="{{ trans_choice('base.review_rating', $rating, ['rating' => $rating]) }}">
                                <span>{{ str_repeat('★', $rating) }}</span><span class="bona-review-card__stars-muted">{{ str_repeat('★', 5 - $rating) }}</span>
                            </div>
                            <p class="bona-review-card__text">{{ $testimonial->display_review ?? $testimonial->review }}</p>
                            <div class="bona-review-card__meta">
                                <div class="bona-review-card__identity">
                                    @if(filled($testimonial->author_avatar_url ?? null))
                                        <img class="bona-review-card__avatar" src="{{ $testimonial->author_avatar_url }}" alt="" width="44" height="44" loading="lazy" referrerpolicy="no-referrer">
                                    @endif
                                    <div>
                                        @if(filled($testimonial->url))
                                            <a class="bona-review-card__author" href="{{ $testimonial->url }}" target="_blank" rel="noopener noreferrer nofollow">
                                                {{ $testimonial->name }}
                                            </a>
                                        @else
                                            <span class="bona-review-card__author">{{ $testimonial->name }}</span>
                                        @endif

                                        @if($isGoogleReview)
                                            <span class="bona-review-card__source">Google Maps</span>
                                        @endif
                                    </div>
                                </div>

                                @if(filled($testimonial->date))
                                    <time class="bona-review-card__date" datetime="{{ $testimonial->date }}">
                                        {{ \Illuminate\Support\Carbon::parse($testimonial->date)->format('d.m.Y') }}
                                    </time>
                                @elseif(filled($testimonial->source_published_label ?? null))
                                    <span class="bona-review-card__date">{{ $testimonial->source_published_label }}</span>
                                @endif
                            </div>
                        </article>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bona-reviews__cta">
                <button class="bona-outline-link" type="button" data-lead-modal-open="dialog-home-review">
                    {{ trans('base.home_leave_review') }}
                </button>
            </div>
        </div>
    </section>

    <x-store.home-review-modal />
@endif
