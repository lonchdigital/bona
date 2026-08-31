@php
    $steps = collect(range(1, 6))->map(fn (int $number) => [
        'number' => str_pad((string) $number, 2, '0', STR_PAD_LEFT),
        'title' => trans("base.home_step_{$number}_title"),
        'text' => trans("base.home_step_{$number}_text"),
    ]);
@endphp

<section class="bona-steps" aria-labelledby="home-steps-title">
    <div class="bona-shell">
        <header class="bona-section-heading">
            <p class="bona-kicker">{{ trans('base.home_steps_kicker') }}</p>
            <h2 id="home-steps-title">{{ trans('base.home_steps_title') }}</h2>
        </header>
        <div class="bona-steps__grid">
            @foreach($steps as $step)
                <article class="bona-step-card">
                    <span class="bona-step-card__number" aria-hidden="true">{{ $step['number'] }}</span>
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                </article>
            @endforeach
        </div>
        <div class="bona-steps__action">
            <a class="bona-button bona-button--dark" href="#dialog-call-measurer" data-fancybox data-src="#dialog-call-measurer">
                {{ trans('base.call_measurer') }}
            </a>
        </div>
    </div>
</section>
