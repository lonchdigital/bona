@extends('layouts.store-main')

@php
    $workName = (string) $work->name;
    $workIntro = (string) $work->intro;
    $workDescription = (string) $work->description;
    $workQuote = (string) $work->client_quote;
    $workMetaDescription = (string) ($work->meta_description ?: $workIntro);
    $workPageTitle = $work->meta_title ?: $workName.' — '.trans('base.our_works');
    $workUrl = url()->current();
    $workHomeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $worksListUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page');
    $serviceTitle = (string) $work->service_title;
    $priceFrom = $work->price_from !== null ? (float) $work->price_from : null;
    $hasFacts = filled($work->location) || filled($work->doors_count) || filled($work->duration);
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;

    $galleryUrls = $work->images
        ->map(fn ($image) => $image->image_url ? url($image->image_url) : null)
        ->filter()
        ->values()
        ->all();

    if ($work->og_image_url) {
        array_unshift($galleryUrls, $work->og_image_url);
    }

    $workSchema = array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'CreativeWork',
        '@id' => $workUrl.'#project',
        'url' => $workUrl,
        'name' => $workName,
        'headline' => $workName,
        'description' => $workMetaDescription ?: null,
        'image' => $galleryUrls ?: null,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'dateCreated' => $work->created_at?->toAtomString(),
        'contentLocation' => $work->location ? ['@type' => 'Place', 'name' => $work->location] : null,
        'creator' => ['@id' => app(App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        'review' => ($workQuote && $work->client_name) ? [
            '@type' => 'Review',
            'reviewBody' => $workQuote,
            'author' => ['@type' => 'Person', 'name' => $work->client_name],
        ] : null,
    ]);

    $serviceSchema = $serviceTitle ? array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => $workUrl.'#service',
        'name' => $serviceTitle,
        'description' => (string) $work->service_description ?: null,
        'serviceType' => $serviceTitle,
        'provider' => ['@id' => app(App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        'areaServed' => array_map(
            fn (string $area) => ['@type' => 'AdministrativeArea', 'name' => $area],
            (array) config('organization.area_served', [])
        ),
        'isRelatedTo' => ['@id' => $workUrl.'#project'],
        'offers' => $priceFrom ? array_filter([
            '@type' => 'Offer',
            'price' => $priceFrom,
            'priceCurrency' => $work->price_currency ?: 'UAH',
            'availability' => 'https://schema.org/InStock',
            'url' => $workUrl,
            'description' => (string) $work->price_note ?: null,
        ]) : null,
    ]) : null;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $workPageTitle)
@section('meta_description', $workMetaDescription)
@section('meta_keywords', $work->meta_keywords ?: '')
@section('og_title', $workName.' — '.trans('base.site_title'))
@section('og_description', $workMetaDescription)
@section('og_type', 'article')

@if($work->og_image_url)
    @section('og_image', $work->og_image_url)
@endif

@push('structured_data')
    <script type="application/ld+json">{!! json_encode($workSchema, $schemaFlags) !!}</script>
    @if($serviceSchema)
        <script type="application/ld+json">{!! json_encode($serviceSchema, $schemaFlags) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($workHomeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => trans('base.our_works'), 'item' => url($worksListUrl)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $workName, 'item' => $workUrl],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-work-detail-page">
        <x-store.content-breadcrumbs :items="[
            ['label' => trans('base.our_works'), 'url' => $worksListUrl],
            ['label' => $workName],
        ]" />

        <section class="bona-work-detail-hero" aria-labelledby="work-page-title">
            <div class="bona-shell bona-work-detail-hero__grid">
                <div>
                    <p class="bona-content-kicker">{{ trans('base.content_project_kicker') }}</p>
                    <h1 id="work-page-title">{{ $workName }}</h1>
                </div>

                @if($workIntro || $hasFacts)
                    <div>
                        @if($workIntro)
                            <p class="bona-content-kicker">{{ trans('base.work_task') }}</p>
                            <p class="bona-work-detail-hero__lead">{{ $workIntro }}</p>
                        @endif

                        @if($hasFacts)
                            <ul class="bona-work-facts">
                                @if($work->location)
                                    <li><span>{{ trans('base.work_location') }}</span><strong>{{ $work->location }}</strong></li>
                                @endif
                                @if($work->doors_count)
                                    <li><span>{{ trans('base.work_doors') }}</span><strong>{{ $work->doors_count }}</strong></li>
                                @endif
                                @if($work->duration)
                                    <li><span>{{ trans('base.work_duration') }}</span><strong>{{ $work->duration }}</strong></li>
                                @endif
                            </ul>
                        @endif
                    </div>
                @endif
            </div>
        </section>

        @if($work->image_url)
            <section class="bona-work-cover">
                <div class="bona-shell">
                    <figure>
                        <img
                            src="{{ $work->image_url }}"
                            alt="{{ $workName }}{{ $work->location ? ', '.$work->location : '' }}"
                            width="1440"
                            height="774"
                            decoding="async"
                        >
                    </figure>
                </div>
            </section>
        @endif

        @if(filled(strip_tags($workDescription)))
            <section class="bona-work-narrative" aria-labelledby="work-solution-title">
                <div class="bona-shell">
                    <div class="bona-work-narrative__section">
                        <div>
                            <p class="bona-content-kicker">{{ trans('base.content_solution_kicker') }}</p>
                            <h2 id="work-solution-title">{{ trans('base.work_solution') }}</h2>
                        </div>
                        <div class="bona-content-richtext">{!! $workDescription !!}</div>
                    </div>
                </div>
            </section>
        @endif

        @if($serviceTitle || $priceFrom)
            <section class="bona-work-service" aria-labelledby="work-service-title">
                <div class="bona-shell bona-work-service__grid">
                    <div>
                        <p class="bona-content-kicker">{{ trans('base.content_service_kicker') }}</p>
                        <h2 id="work-service-title">{{ trans('base.work_service') }}</h2>
                        @if($serviceTitle)<p class="bona-work-service__name">{{ $serviceTitle }}</p>@endif
                        @if($work->service_description)<p class="bona-work-service__description">{{ $work->service_description }}</p>@endif
                    </div>
                    @if($priceFrom)
                        <div class="bona-work-service__price">
                            <small>{{ trans('base.work_price_from') }}</small>
                            <strong>{{ number_format($priceFrom, 0, ',', ' ') }} {{ $work->price_currency ?: 'UAH' }}</strong>
                            @if($work->price_note)<span>{{ $work->price_note }}</span>@endif
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @if($work->images->isNotEmpty())
            <section class="bona-work-gallery" aria-labelledby="work-gallery-title">
                <div class="bona-shell">
                    <header class="bona-content-heading">
                        <div>
                            <p class="bona-content-kicker">{{ trans('base.content_gallery_kicker') }}</p>
                            <h2 id="work-gallery-title">{{ trans('base.work_gallery') }}</h2>
                        </div>
                    </header>
                    <ul class="bona-work-gallery__grid">
                        @foreach($work->images as $image)
                            <li class="bona-work-gallery__item">
                                <a data-fancybox="work-gallery" href="{{ $image->image_url }}">
                                    <img
                                        src="{{ $image->image_url }}"
                                        alt="{{ $image->caption ?: $workName }}{{ $work->location ? ', '.$work->location : '' }}"
                                        width="1400"
                                        height="920"
                                        loading="lazy"
                                        decoding="async"
                                    >
                                </a>
                                @if($image->caption)<figcaption>{{ $image->caption }}</figcaption>@endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            </section>
        @endif

        @if($workQuote)
            <section class="bona-work-quote" aria-label="{{ trans('base.content_client_quote') }}">
                <div class="bona-shell">
                    <blockquote>
                        <p>“{{ $workQuote }}”</p>
                        @if($work->client_name)<footer>{{ $work->client_name }}</footer>@endif
                    </blockquote>
                </div>
            </section>
        @endif

        <section class="bona-work-actions" aria-labelledby="work-action-title">
            <div class="bona-shell bona-work-actions__panel">
                <h2 id="work-action-title">{{ trans('base.content_project_cta_title') }}</h2>
                <a class="bona-button bona-button--dark" href="#dialog-call-measurer" data-lead-modal-open="dialog-call-measurer">
                    {{ trans('base.call_measurer') }}
                </a>
            </div>
        </section>

        @if($otherWorks->isNotEmpty())
            <section class="bona-other-works" aria-labelledby="other-works-title">
                <div class="bona-shell">
                    <header class="bona-content-heading">
                        <div>
                            <p class="bona-content-kicker">{{ trans('base.content_other_works_kicker') }}</p>
                            <h2 id="other-works-title">{{ trans('base.work_other') }}</h2>
                        </div>
                    </header>
                    <div class="bona-works-list__grid">
                        @foreach($otherWorks as $otherWork)
                            @include('pages.works.partials.work_item', ['work' => $otherWork, 'headingLevel' => 'h3'])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
