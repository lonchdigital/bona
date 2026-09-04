@extends('layouts.store-main')

@php
    $authorName = (string) $author->name;
    $authorJobTitle = (string) $author->job_title;
    $authorShortDescription = (string) $author->short_description;
    $authorMetaDescription = (string) ($author->meta_description ?: $authorShortDescription);

    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $authorUrl = url()->current();
@endphp

@section('title')
    <title>{{ $author->meta_title ?: $authorName . ' — ' . $authorJobTitle }}</title>
    <meta name="title" content="{{ $author->meta_title ?: $authorName }}">

    @if($authorMetaDescription)
        <meta name="description" content="{{ $authorMetaDescription }}">
    @endif

    @if($author->meta_keywords)
        <meta name="keywords" content="{{ $author->meta_keywords }}">
    @endif

    <meta property="og:title" content="{{ $authorName . ' — ' . trans('base.site_title') }}">

    @if($authorMetaDescription)
        <meta property="og:description" content="{{ $authorMetaDescription }}">
    @endif

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $authorName }}">

    @if($authorMetaDescription)
        <meta name="twitter:description" content="{{ $authorMetaDescription }}">
    @endif

    @if($author->og_image_url)
        <meta name="twitter:image" content="{{ $author->og_image_url }}">
    @endif
@endsection

@section('og_type', 'profile')

@if($author->og_image_url)
    @section('og_image', $author->og_image_url)
@endif

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => $authorName]])

    @php
        $personSchema = array_filter([
            '@'.'context' => 'https://schema.org',
            '@type' => 'Person',
            '@id' => $authorUrl . '#person',
            'name' => $authorName,
            'url' => $authorUrl,
            'jobTitle' => $authorJobTitle ?: null,
            'description' => $authorMetaDescription ?: null,
            'image' => $author->og_image_url,
            'sameAs' => $author->sameAsLinks() ?: null,
            'worksFor' => [
                '@type' => 'Organization',
                'name' => trans('base.organization'),
                'url' => url('/'),
            ],
            'hasCredential' => $author->certificates
                ->map(fn ($certificate) => array_filter([
                    '@type' => 'EducationalOccupationalCredential',
                    'name' => (string) $certificate->title ?: null,
                    'credentialCategory' => trans('base.author_certificates'),
                    'recognizedBy' => $certificate->issuer
                        ? ['@type' => 'Organization', 'name' => $certificate->issuer]
                        : null,
                    'validFrom' => $certificate->issued_year ? (string) $certificate->issued_year : null,
                    'image' => $certificate->image_url ? url($certificate->image_url) : null,
                ]))
                ->filter(fn ($credential) => count($credential) > 2)
                ->values()
                ->all() ?: null,
        ]);

        $breadcrumbSchema = [
            '@'.'context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $authorName, 'item' => $authorUrl],
            ],
        ];

        $profilePageSchema = [
            '@'.'context' => 'https://schema.org',
            '@type' => 'ProfilePage',
            'mainEntity' => ['@id' => $authorUrl . '#person'],
            'url' => $authorUrl,
            'inLanguage' => app()->getLocale(),
        ];

        $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
    @endphp

    <script type="application/ld+json">{!! json_encode($personSchema, $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($profilePageSchema, $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, $schemaFlags) !!}</script>

    <section class="art-section-pd art-author-page">
        <div class="container">

            <div class="row art-author-intro">
                <div class="col-lg-4">
                    @if($author->photo_url)
                        <div class="art-author-photo">
                            <img src="{{ $author->photo_url }}" alt="{{ $authorName }}{{ $authorJobTitle ? ', ' . $authorJobTitle : '' }}">
                        </div>
                    @endif
                </div>

                <div class="col-lg-8">
                    <h1 class="title art-author-name">{{ $authorName }}</h1>

                    @if($authorJobTitle)
                        <p class="art-author-job-title">{{ $authorJobTitle }}</p>
                    @endif

                    @if($authorShortDescription)
                        <p class="art-author-short">{{ $authorShortDescription }}</p>
                    @endif

                    @if($author->sameAsLinks())
                        <ul class="art-author-links">
                            @if($author->instagram_url)
                                <li><a href="{{ $author->instagram_url }}" target="_blank" rel="noopener nofollow">Instagram</a></li>
                            @endif
                            @if($author->facebook_url)
                                <li><a href="{{ $author->facebook_url }}" target="_blank" rel="noopener nofollow">Facebook</a></li>
                            @endif
                            @if($author->linkedin_url)
                                <li><a href="{{ $author->linkedin_url }}" target="_blank" rel="noopener nofollow">LinkedIn</a></li>
                            @endif
                        </ul>
                    @endif
                </div>
            </div>

            @if($author->biography)
                <div class="row">
                    <div class="col-12">
                        <h2 class="art-author-section-title">{{ trans('base.author_biography') }}</h2>
                        <div class="art-author-biography">{!! $author->biography !!}</div>
                    </div>
                </div>
            @endif

            @if($author->certificates->count())
                <div class="row">
                    <div class="col-12">
                        <h2 class="art-author-section-title">{{ trans('base.author_certificates') }}</h2>

                        <ul class="art-author-certificates">
                            @foreach($author->certificates as $certificate)
                                <li class="art-author-certificate">
                                    <a href="{{ $certificate->image_url }}" target="_blank" rel="noopener">
                                        <img src="{{ $certificate->image_url }}"
                                             alt="{{ $certificate->title ?: trans('base.author_certificates') }}{{ $certificate->issuer ? ', ' . $certificate->issuer : '' }}"
                                             loading="lazy">
                                    </a>

                                    @if($certificate->title || $certificate->issuer || $certificate->issued_year)
                                        <div class="art-author-certificate__caption">
                                            @if($certificate->title)
                                                <span class="art-author-certificate__title">{{ $certificate->title }}</span>
                                            @endif
                                            @if($certificate->issuer)
                                                <span class="art-author-certificate__issuer">{{ $certificate->issuer }}</span>
                                            @endif
                                            @if($certificate->issued_year)
                                                <span class="art-author-certificate__year">{{ $certificate->issued_year }}</span>
                                            @endif
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($authorArticles->count())
                <div class="row">
                    <div class="col-12">
                        <h2 class="art-author-section-title">{{ trans('base.author_articles') }}</h2>
                    </div>
                </div>

                <div class="row blog">
                    <div class="art-blog-archive-wrapper">
                        @foreach($authorArticles as $article)
                            <div class="col-lg-4">
                                @include('pages.store.partials.article_item', ['article' => $article])
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

@endsection

@push('head')
    <style>
        .art-author-intro {
            align-items: center;
            margin-bottom: 45px;
        }

        .art-author-photo img {
            width: 100%;
            max-width: 320px;
            border-radius: 6px;
        }

        .art-author-name {
            margin-bottom: 10px;
        }

        .art-author-job-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 15px;
        }

        .art-author-short {
            font-weight: 300;
            margin-bottom: 20px;
        }

        .art-author-links {
            display: flex;
            flex-wrap: wrap;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .art-author-links li {
            margin: 0 20px 0 0;
        }

        .art-author-section-title {
            border-top: 1px solid #dddddd;
            padding-top: 30px;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        .art-author-biography {
            font-weight: 300;
        }

        .art-author-certificates {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 24px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .art-author-certificate img {
            width: 100%;
            height: 280px;
            object-fit: contain;
            background-color: #f5f5f5;
            border: 1px solid #eeeeee;
            padding: 10px;
        }

        .art-author-certificate__caption {
            padding-top: 10px;
            font-size: 13px;
            line-height: 1.4;
        }

        .art-author-certificate__caption span {
            display: block;
        }

        .art-author-certificate__title {
            font-weight: 500;
        }

        .art-author-certificate__issuer,
        .art-author-certificate__year {
            font-weight: 300;
            color: #777777;
        }
    </style>
@endpush
