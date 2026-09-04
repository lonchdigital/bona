@extends('layouts.store-main')

@php
    $aboutTitle = trans('base.about_us');
    $aboutDescription = trim((string) ($aboutUsConfig->meta_description ?: preg_replace(
        '/\s+/u',
        ' ',
        html_entity_decode(strip_tags((string) $aboutUsConfig->description))
    )));
    $aboutLead = Illuminate\Support\Str::limit($aboutDescription, 240);
    $aboutPageTitle = $aboutUsConfig->meta_title ?: $aboutTitle.' — '.trans('base.site_title');
    $hasIntroMedia = filled($aboutUsConfig->iframe) || filled($aboutUsConfig->image);
    $hasIntroContent = $hasIntroMedia
        || filled($aboutUsConfig->title)
        || filled(strip_tags((string) $aboutUsConfig->description))
        || filled($aboutUsConfig->button_url);
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $aboutPageTitle)
@section('meta_description', $aboutDescription)
@section('meta_keywords', $aboutUsConfig->meta_keywords ?: '')
@section('og_title', $aboutPageTitle)
@section('og_description', $aboutDescription)

@if($aboutUsConfig->image)
    @section('og_image', App\Helpers\PreviewImage::url($aboutUsConfig->image))
@endif

@push('head')
    @if($aboutUsConfig->meta_tags)
        {!! $aboutUsConfig->meta_tags !!}
    @endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'AboutPage',
        '@id' => url()->current().'#about-page',
        'url' => url()->current(),
        'name' => $aboutPageTitle,
        'description' => $aboutDescription ?: null,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'mainEntity' => ['@id' => app(App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $aboutTitle, 'item' => url()->current()],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-about-page">
        <x-store.content-breadcrumbs :items="[['label' => $aboutTitle]]" />

        <section class="bona-content-hero" aria-labelledby="about-page-title">
            <div class="bona-shell bona-content-hero__grid">
                <div class="bona-content-hero__copy">
                    <p class="bona-content-kicker">{{ trans('base.content_about_kicker') }}</p>
                    <h1 id="about-page-title">{{ $aboutTitle }}</h1>
                </div>
                @if($aboutLead)
                    <p class="bona-content-hero__lead">{{ $aboutLead }}</p>
                @endif
            </div>
        </section>

        @if($hasIntroContent)
            <section class="bona-content-feature{{ $hasIntroMedia ? '' : ' bona-content-feature--text-only' }}">
                <div class="bona-shell bona-content-feature__grid">
                    @if($hasIntroMedia)
                        <div class="bona-content-feature__media">
                            @if(filled($aboutUsConfig->iframe))
                                {!! $aboutUsConfig->iframe !!}
                            @elseif($aboutUsConfig->image)
                                <img
                                    src="{{ $aboutUsConfig->imageUrl }}"
                                    alt="{{ $aboutUsConfig->title ?: $aboutTitle }}"
                                    width="760"
                                    height="850"
                                    decoding="async"
                                >
                            @endif
                        </div>
                    @endif

                    <div class="bona-content-feature__copy">
                        @if($aboutUsConfig->title)
                            <p class="bona-content-kicker">{{ trans('base.content_about_intro_kicker') }}</p>
                            <h2>{{ $aboutUsConfig->title }}</h2>
                        @endif
                        @if(filled(strip_tags((string) $aboutUsConfig->description)))
                            <div class="bona-content-richtext">{!! $aboutUsConfig->description !!}</div>
                        @endif
                        @if($aboutUsConfig->button_url && $aboutUsConfig->button_text)
                            @php
                                $aboutButtonExternal = Illuminate\Support\Str::startsWith($aboutUsConfig->button_url, ['http://', 'https://']);
                            @endphp
                            <div class="bona-content-inline-action">
                                <a
                                    class="bona-button bona-button--dark"
                                    href="{{ $aboutUsConfig->button_url }}"
                                    @if($aboutButtonExternal) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    {{ $aboutUsConfig->button_text }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if($aboutUsFacts->isNotEmpty())
            <section class="bona-content-facts" aria-labelledby="about-facts-title">
                <div class="bona-shell">
                    <div class="bona-content-facts__panel">
                        <p class="bona-content-kicker">{{ trans('base.content_facts_kicker') }}</p>
                        <h2 id="about-facts-title">{{ $aboutUsConfig->facts_title ?: trans('base.content_facts_title') }}</h2>
                        <ul class="bona-content-facts__grid">
                            @foreach($aboutUsFacts as $fact)
                                <li class="bona-content-facts__item">
                                    <span class="bona-content-facts__value">{{ $fact->value }}</span>
                                    @if($fact->label)
                                        <span class="bona-content-facts__label">{{ $fact->label }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </section>
        @endif

        @if(filled(strip_tags((string) $aboutUsConfig->history_text)))
            <section class="bona-content-story" aria-labelledby="about-story-title">
                <div class="bona-shell bona-content-story__grid">
                    <div>
                        <p class="bona-content-kicker">{{ trans('base.content_story_kicker') }}</p>
                        <h2 id="about-story-title">{{ $aboutUsConfig->history_title ?: trans('base.content_story_title') }}</h2>
                    </div>
                    <div class="bona-content-richtext">{!! $aboutUsConfig->history_text !!}</div>
                </div>
            </section>
        @endif

        @if($aboutUsSteps->isNotEmpty())
            <section class="bona-content-process" aria-labelledby="about-process-title">
                <div class="bona-shell">
                    <header class="bona-content-heading">
                        <div>
                            <p class="bona-content-kicker">{{ trans('base.content_process_kicker') }}</p>
                            <h2 id="about-process-title">{{ $aboutUsConfig->steps_title ?: trans('base.content_process_title') }}</h2>
                        </div>
                    </header>
                    <ol class="bona-content-process__list">
                        @foreach($aboutUsSteps as $step)
                            <li class="bona-content-process__item">
                                <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                <h3>{{ $step->title }}</h3>
                                @if($step->text)
                                    <p>{{ $step->text }}</p>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </div>
            </section>
        @endif

        @if($aboutUsTeam->isNotEmpty())
            <section class="bona-content-team" aria-labelledby="about-team-title">
                <div class="bona-shell">
                    <header class="bona-content-heading">
                        <div>
                            <p class="bona-content-kicker">{{ trans('base.content_team_kicker') }}</p>
                            <h2 id="about-team-title">{{ $aboutUsConfig->team_title ?: trans('base.content_team_title') }}</h2>
                        </div>
                    </header>
                    <ul class="bona-content-team__grid">
                        @foreach($aboutUsTeam as $member)
                            <li class="bona-team-member">
                                @if($member->photo_url)
                                    <div class="bona-team-member__portrait">
                                        <img
                                            src="{{ $member->photo_url }}"
                                            alt="{{ $member->name }}{{ $member->role ? ', '.$member->role : '' }}"
                                            width="520"
                                            height="650"
                                            loading="lazy"
                                            decoding="async"
                                        >
                                    </div>
                                @endif
                                <div class="bona-team-member__body">
                                    <h3>{{ $member->name }}</h3>
                                    @if($member->role)<span class="bona-team-member__role">{{ $member->role }}</span>@endif
                                    @if($member->experience)<span class="bona-team-member__experience">{{ $member->experience }}</span>@endif
                                    @if($member->quote)<p class="bona-team-member__quote">“{{ $member->quote }}”</p>@endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if($aboutUsConfig->cta_title || $aboutUsConfig->cta_text)
            @php
                $aboutCtaUrl = $aboutUsConfig->cta_button_url ?: '#dialog-call-measurer';
                $aboutCtaExternal = Illuminate\Support\Str::startsWith($aboutCtaUrl, ['http://', 'https://']);
            @endphp
            <section class="bona-content-cta" aria-labelledby="about-cta-title">
                <div class="bona-shell">
                    <div class="bona-content-cta__panel">
                        <div>
                            @if($aboutUsConfig->cta_title)
                                <h2 id="about-cta-title">{{ $aboutUsConfig->cta_title }}</h2>
                            @endif
                            @if($aboutUsConfig->cta_text)<p>{{ $aboutUsConfig->cta_text }}</p>@endif
                        </div>
                        @if($aboutUsConfig->cta_button_text)
                            <a
                                class="bona-button bona-button--light"
                                href="{{ $aboutCtaUrl }}"
                                @if(!$aboutUsConfig->cta_button_url) data-lead-modal-open="dialog-call-measurer" @endif
                                @if($aboutCtaExternal) target="_blank" rel="noopener noreferrer" @endif
                            >
                                {{ $aboutUsConfig->cta_button_text }}
                            </a>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <x-store.home-partners
            :brands="$brands"
            :section="[
                'kicker' => trans('base.partners_kicker'),
                'title' => trans('base.our_partners'),
            ]"
        />

        <x-store.home-blog
            :articles="$articles"
            :section="[
                'kicker' => trans('base.blog_latest'),
                'title' => trans('base.blog'),
                'link_label' => trans('base.blog_all'),
                'link_url' => App\Helpers\MultiLangRoute::getMultiLangRoute('blog.main.page'),
            ]"
        />
    </div>
@endsection
