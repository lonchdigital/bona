<section class="bona-configurator-promo" aria-labelledby="home-configurator-title" data-home-configurator>
    <div class="bona-shell bona-configurator-promo__grid">
        <div class="bona-configurator-promo__content">
            <p class="bona-configurator-promo__kicker">{{ trans('configurator.promo.kicker') }}</p>
            <h2 id="home-configurator-title">{{ trans('configurator.promo.heading') }}</h2>
            <p class="bona-configurator-promo__description">{{ trans('configurator.promo.description') }}</p>
            <ul class="bona-configurator-promo__features">
                @foreach(trans('configurator.promo.features') as $feature)
                    <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="1.5" /></svg>{{ $feature }}</li>
                @endforeach
            </ul>
            <a class="bona-configurator-promo__cta" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.door-configurator.page') }}">
                {{ trans('configurator.promo.cta') }}
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" /></svg>
            </a>
            <p class="bona-configurator-promo__note">{{ trans('configurator.promo.note') }}</p>
        </div>
        <figure class="bona-configurator-promo__preview">
            {{-- Same room, product crop and proportions as the live configurator. No simulated controls. --}}
            <div class="bona-configurator-promo__scene">
                <img class="bona-configurator-promo__room" src="{{ asset('assets/door-configurator/v1/room-living-v5.webp') }}" alt="{{ trans('configurator.promo.image_alt') }}" width="1536" height="1024" loading="lazy" decoding="async">
                <div class="bona-configurator-promo__door" aria-hidden="true">
                    <img src="{{ asset('assets/door-configurator/v1/new-york-ivory.jpg') }}" alt="" width="800" height="837" loading="lazy" decoding="async">
                </div>
            </div>
            <figcaption>
                <span>{{ trans('configurator.promo.caption') }}</span>
                <span>{{ trans('configurator.promo.finish') }}</span>
            </figcaption>
        </figure>
    </div>
</section>
