@php
    $projectUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.work.page', ['workSlug' => $work->slug]);
    $headingTag = in_array($headingLevel ?? 'h2', ['h2', 'h3'], true) ? ($headingLevel ?? 'h2') : 'h2';
@endphp

<article class="bona-project-card">
    <a class="bona-project-card__image" href="{{ $projectUrl }}" aria-label="{{ $work->name }}">
        @if($work->image_url)
            <img
                src="{{ $work->image_url }}"
                alt="{{ $work->name }}{{ $work->location ? ', '.$work->location : '' }}"
                width="720"
                height="540"
                loading="lazy"
                decoding="async"
            >
        @endif
    </a>

    <div class="bona-project-card__body">
        @if($work->location || $work->doors_count)
            <div class="bona-project-card__meta">
                @if($work->location)<span>{{ $work->location }}</span>@endif
                @if($work->doors_count)<span>{{ trans('base.work_doors_count', ['COUNT' => $work->doors_count]) }}</span>@endif
            </div>
        @endif

        <{{ $headingTag }}><a href="{{ $projectUrl }}">{{ $work->name }}</a></{{ $headingTag }}>

        @if($work->intro)
            <p class="bona-project-card__intro">{{ $work->intro }}</p>
        @endif

        <a class="bona-project-card__link" href="{{ $projectUrl }}">
            {{ trans('base.content_view_project') }} <span aria-hidden="true">→</span>
        </a>
    </div>
</article>
