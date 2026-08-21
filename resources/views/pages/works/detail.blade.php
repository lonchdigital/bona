@extends('layouts.store-main')

@php
    $workName = (string) $work->name;
    $workIntro = (string) $work->intro;
    $workDescription = (string) $work->description;
    $workQuote = (string) $work->client_quote;
    $workMetaDescription = (string) ($work->meta_description ?: $workIntro);

    $workUrl = url()->current();
    $workHomeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $worksListUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.works.page');
@endphp

@section('title')
    <title>{{ $work->meta_title ?: $workName . ' — ' . trans('base.our_works') }}</title>
    <meta name="title" content="{{ $work->meta_title ?: $workName }}">

    @if($workMetaDescription)
        <meta name="description" content="{{ $workMetaDescription }}">
        <meta property="og:description" content="{{ $workMetaDescription }}">
        <meta name="twitter:description" content="{{ $workMetaDescription }}">
    @endif

    @if($work->meta_keywords)
        <meta name="keywords" content="{{ $work->meta_keywords }}">
    @endif

    <meta property="og:title" content="{{ $workName . ' — ' . trans('base.site_title') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $workName }}">

    @if($work->og_image_url)
        <meta name="twitter:image" content="{{ $work->og_image_url }}">
    @endif
@endsection

@section('og_type', 'article')

@if($work->og_image_url)
    @section('og_image', $work->og_image_url)
@endif

@section('content')

    @include('pages.store.partials.page_header', ['links' => [
        $worksListUrl => trans('base.our_works'),
        'own' => $workName,
    ]])

    @php
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
            '@context' => 'https://schema.org',
            '@type' => 'CreativeWork',
            '@id' => $workUrl . '#project',
            'url' => $workUrl,
            'name' => $workName,
            'headline' => $workName,
            'description' => $workMetaDescription ?: null,
            'image' => $galleryUrls ?: null,
            'inLanguage' => app()->getLocale(),
            'dateCreated' => $work->created_at?->toAtomString(),
            'contentLocation' => $work->location ? ['@type' => 'Place', 'name' => $work->location] : null,
            'creator' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
            'review' => ($workQuote && $work->client_name) ? [
                '@type' => 'Review',
                'reviewBody' => $workQuote,
                'author' => ['@type' => 'Person', 'name' => $work->client_name],
            ] : null,
        ]);

        // A project page is a page about work that was sold, so it can be
        // described as an offer rather than only as a picture.
        $serviceTitle = (string) $work->service_title;
        $priceFrom = $work->price_from !== null ? (float) $work->price_from : null;

        $serviceSchema = $serviceTitle ? array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Service',
            '@id' => $workUrl . '#service',
            'name' => $serviceTitle,
            'description' => (string) $work->service_description ?: null,
            'serviceType' => $serviceTitle,
            'provider' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
            'areaServed' => array_map(
                fn (string $area) => ['@type' => 'AdministrativeArea', 'name' => $area],
                (array) config('organization.area_served', [])
            ),
            'isRelatedTo' => ['@id' => $workUrl . '#project'],
            'offers' => $priceFrom ? array_filter([
                '@type' => 'Offer',
                'price' => $priceFrom,
                'priceCurrency' => $work->price_currency ?: 'UAH',
                'availability' => 'https://schema.org/InStock',
                'url' => $workUrl,
                'description' => (string) $work->price_note ?: null,
            ]) : null,
        ]) : null;

        $workBreadcrumbSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($workHomeUrl)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => trans('base.our_works'), 'item' => url($worksListUrl)],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $workName, 'item' => $workUrl],
            ],
        ];
    @endphp

    <script type="application/ld+json">{!! json_encode($workSchema, $schemaFlags) !!}</script>
    @if($serviceSchema)
        <script type="application/ld+json">{!! json_encode($serviceSchema, $schemaFlags) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode($workBreadcrumbSchema, $schemaFlags) !!}</script>

    <section class="blog art-section-pd art-work-page">
        <div class="container">

            <div class="row">
                <div class="col-lg-10 col-md-offset-1">

                    <h1 class="title art-work-page__title">{{ $workName }}</h1>

                    @if($work->location || $work->doors_count || $work->duration)
                        <ul class="art-work-facts">
                            @if($work->location)
                                <li><span class="art-work-facts__label">{{ trans('base.work_location') }}</span>{{ $work->location }}</li>
                            @endif
                            @if($work->doors_count)
                                <li><span class="art-work-facts__label">{{ trans('base.work_doors') }}</span>{{ $work->doors_count }}</li>
                            @endif
                            @if($work->duration)
                                <li><span class="art-work-facts__label">{{ trans('base.work_duration') }}</span>{{ $work->duration }}</li>
                            @endif
                        </ul>
                    @endif

                    @if($work->image_url)
                        <figure class="art-work-cover">
                            <img src="{{ $work->image_url }}" alt="{{ $workName }}{{ $work->location ? ', ' . $work->location : '' }}">
                        </figure>
                    @endif

                    @if($workIntro)
                        <div class="art-work-section">
                            <h2>{{ trans('base.work_task') }}</h2>
                            <p>{{ $workIntro }}</p>
                        </div>
                    @endif

                    @if($workDescription)
                        <div class="art-work-section art-work-solution">
                            <h2>{{ trans('base.work_solution') }}</h2>
                            {!! $workDescription !!}
                        </div>
                    @endif

                    @if($serviceTitle || $priceFrom)
                        <div class="art-work-section art-work-service">
                            <h2>{{ trans('base.work_service') }}</h2>

                            @if($serviceTitle)
                                <p class="art-work-service__name">{{ $serviceTitle }}</p>
                            @endif

                            @if($work->service_description)
                                <p>{{ $work->service_description }}</p>
                            @endif

                            @if($priceFrom)
                                <p class="art-work-service__price">
                                    <span class="art-work-service__price-label">{{ trans('base.work_price_from') }}</span>
                                    <span class="art-work-service__price-value">{{ number_format($priceFrom, 0, ',', ' ') }} {{ $work->price_currency ?: 'UAH' }}</span>
                                    @if($work->price_note)
                                        <span class="art-work-service__price-note">{{ $work->price_note }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>
                    @endif

                    @if($work->images->count())
                        <div class="art-work-section">
                            <h2>{{ trans('base.work_gallery') }}</h2>
                            <ul class="art-work-gallery">
                                @foreach($work->images as $image)
                                    <li>
                                        <a data-fancybox="work-gallery" href="{{ $image->image_url }}">
                                            <img src="{{ $image->image_url }}"
                                                 alt="{{ $image->caption ?: $workName }}{{ $work->location ? ', ' . $work->location : '' }}"
                                                 loading="lazy">
                                        </a>
                                        @if($image->caption)
                                            <figcaption>{{ $image->caption }}</figcaption>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if($workQuote)
                        <blockquote class="art-work-quote">
                            <p>{{ $workQuote }}</p>
                            @if($work->client_name)
                                <footer>{{ $work->client_name }}</footer>
                            @endif
                        </blockquote>
                    @endif

                    <div class="art-work-cta">
                        <a href="" class="btn btn-main" data-fancybox data-src="#dialog-call-measurer">{{ trans('base.call_measurer') }}</a>
                    </div>

                </div>
            </div>

        </div>
    </section>

    @if($otherWorks->count())
        <section class="blog art-single-latest-articles">
            <div class="container">
                <div class="row">
                    <header class="col-12 art-header-left">
                        <div>
                            <h2 class="title">{{ trans('base.work_other') }}</h2>
                        </div>
                    </header>
                </div>
                <div class="row">
                    <div class="art-blog-archive-wrapper">
                        @foreach($otherWorks as $otherWork)
                            <div class="col-lg-4">
                                @include('pages.works.partials.work_item', ['work' => $otherWork])
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
    @endif

@endsection

@push('head')
    <style>
        .art-work-page__title {
            margin-bottom: 20px;
        }

        .art-work-facts {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            margin: 0 0 25px;
            padding: 0;
            border-top: 1px solid #dddddd;
            border-bottom: 1px solid #dddddd;
        }

        .art-work-facts li {
            padding: 14px 30px 14px 0;
            font-weight: 300;
        }

        .art-work-facts__label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            color: #777777;
        }

        .art-work-cover img,
        .art-work-gallery img {
            width: 100%;
            height: auto;
            display: block;
        }

        .art-work-section {
            margin-top: 35px;
        }

        .art-work-section h2 {
            text-transform: none;
            margin-bottom: 15px;
        }

        .art-work-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 20px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .art-work-gallery figcaption {
            padding-top: 8px;
            font-size: 13px;
            font-weight: 300;
            color: #777777;
        }

        .art-work-quote {
            margin: 35px 0 0;
            padding: 6px 0 6px 20px;
            border-left: 3px solid #333333;
            font-weight: 300;
            font-style: italic;
        }

        .art-work-quote footer {
            margin-top: 8px;
            font-style: normal;
            font-weight: 500;
            font-size: 14px;
        }

        .art-work-service__name {
            font-weight: 500;
        }

        .art-work-service__price {
            margin-top: 12px;
            padding: 14px 18px;
            background-color: #f5f5f5;
            display: inline-block;
        }

        .art-work-service__price-label {
            display: block;
            font-size: 13px;
            color: #777777;
        }

        .art-work-service__price-value {
            font-size: 22px;
            font-weight: 500;
        }

        .art-work-service__price-note {
            display: block;
            font-size: 13px;
            font-weight: 300;
            color: #777777;
        }

        .art-work-cta {
            margin-top: 35px;
        }
    </style>
@endpush
