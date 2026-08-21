<article class="art-post-archive-item art-work-item">
    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.work.page', ['workSlug' => $work->slug]) }}">
        <div class="image" style="background-image:url({{ $work->image_url }})">
            <img src="{{ $work->image_url }}" alt="{{ $work->name }}{{ $work->location ? ', ' . $work->location : '' }}" loading="lazy">
        </div>
        <div class="entry entry-post">
            <div class="preview-post-right">
                <div class="title">
                    <span class="h5">{{ $work->name }}</span>
                </div>

                @if($work->location || $work->doors_count)
                    <div class="art-work-item__facts">
                        @if($work->location)<span>{{ $work->location }}</span>@endif
                        @if($work->doors_count)<span>{{ trans('base.work_doors_count', ['COUNT' => $work->doors_count]) }}</span>@endif
                    </div>
                @endif

                @if($work->intro)
                    <div class="art-preview-text"><p>{{ $work->intro }}</p></div>
                @endif
            </div>
        </div>
    </a>
</article>
