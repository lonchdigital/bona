@php
    $ideas = [
        ['image' => 'bedroom', 'title' => trans('base.home_idea_bedroom_title'), 'text' => trans('base.home_idea_bedroom_text')],
        ['image' => 'living', 'title' => trans('base.home_idea_living_title'), 'text' => trans('base.home_idea_living_text')],
        ['image' => 'hall', 'title' => trans('base.home_idea_hall_title'), 'text' => trans('base.home_idea_hall_text')],
    ];
@endphp

<section class="bona-ideas" aria-labelledby="home-ideas-title">
    <div class="bona-shell">
        <header class="bona-section-heading">
            <p class="bona-kicker">{{ trans('base.home_ideas_kicker') }}</p>
            <h2 id="home-ideas-title">{{ trans('base.home_ideas_title') }}</h2>
        </header>
        <div class="bona-ideas__grid">
            @foreach($ideas as $idea)
                <article class="bona-idea-card">
                    <span
                        class="bona-idea-card__image bona-idea-card__image--{{ $idea['image'] }}"
                        role="img"
                        aria-label="{{ $idea['title'] }}"
                    ></span>
                    <h3>{{ $idea['title'] }}</h3>
                    <p>{{ $idea['text'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
