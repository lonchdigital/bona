@props(['works' => collect()])

@php
    $fallbackWorks = [
        ['image' => 'apartment', 'title' => trans('base.home_work_apartment_title'), 'text' => trans('base.home_work_apartment_text')],
        ['image' => 'house', 'title' => trans('base.home_work_house_title'), 'text' => trans('base.home_work_house_text')],
        ['image' => 'office', 'title' => trans('base.home_work_office_title'), 'text' => trans('base.home_work_office_text')],
    ];
@endphp

<section class="bona-works" aria-labelledby="home-works-title">
    <div class="bona-shell">
        <header class="bona-section-heading bona-section-heading--split">
            <div>
                <p class="bona-kicker">{{ trans('base.home_works_kicker') }}</p>
                <h2 id="home-works-title">{{ trans('base.our_works') }}</h2>
            </div>
            <a class="bona-text-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page') }}">
                {{ trans('base.home_all_projects') }} <span aria-hidden="true">→</span>
            </a>
        </header>

        <div class="bona-works__grid">
            @forelse($works as $work)
                <article class="bona-work-card">
                    <a class="bona-work-card__image" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.work.page', ['workSlug' => $work->slug]) }}">
                        <img src="{{ $work->image_url }}" alt="{{ $work->name }}" loading="lazy">
                    </a>
                    <h3><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.work.page', ['workSlug' => $work->slug]) }}">{{ $work->name }}</a></h3>
                    @if($work->intro || $work->location)
                        <p>{{ $work->intro ?: $work->location }}</p>
                    @endif
                </article>
            @empty
                @foreach($fallbackWorks as $work)
                    <article class="bona-work-card">
                        <span
                            class="bona-work-card__image bona-work-card__image--{{ $work['image'] }}"
                            role="img"
                            aria-label="{{ $work['title'] }}"
                        ></span>
                        <h3>{{ $work['title'] }}</h3>
                        <p>{{ $work['text'] }}</p>
                    </article>
                @endforeach
            @endforelse
        </div>
    </div>
</section>
