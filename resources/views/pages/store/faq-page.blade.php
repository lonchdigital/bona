@extends('layouts.store-main')

@php
    $faqTitle = trans('base.faq_page_title');
    $faqIntro = trans('base.faq_page_intro');
    $faqUrl = url()->current();
    $faqHomeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
@endphp

@section('title')
    <title>{{ $faqTitle . ' — ' . trans('base.site_title') }}</title>
    <meta name="title" content="{{ $faqTitle }}">
    <meta name="description" content="{{ $faqIntro }}">

    <meta property="og:title" content="{{ $faqTitle . ' — ' . trans('base.site_title') }}">
    <meta property="og:description" content="{{ $faqIntro }}">
    <meta name="twitter:card" content="summary_large_image">
@endsection

@section('content')

    @include('pages.store.partials.page_header', ['links' => ['own' => $faqTitle]])

    @php
        $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;

        $faqQuestions = [];

        foreach ($faqGroups as $group) {
            foreach ($group['items'] as $item) {
                $question = trim((string) $item->question);
                $answer = trim((string) $item->answer);

                if ($question === '' || $answer === '') {
                    continue;
                }

                $faqQuestions[] = [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => ['@type' => 'Answer', 'text' => $answer],
                ];
            }
        }

        $faqSchema = $faqQuestions ? [
            '@'.'context' => 'https://schema.org',
            '@type' => 'FAQPage',
            '@id' => $faqUrl . '#faq',
            'url' => $faqUrl,
            'name' => $faqTitle,
            'description' => $faqIntro,
            'inLanguage' => app()->getLocale(),
            'mainEntity' => $faqQuestions,
        ] : null;

        $faqBreadcrumbSchema = [
            '@'.'context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($faqHomeUrl)],
                ['@type' => 'ListItem', 'position' => 2, 'name' => $faqTitle, 'item' => $faqUrl],
            ],
        ];
    @endphp

    @if($faqSchema)
        <script type="application/ld+json">{!! json_encode($faqSchema, $schemaFlags) !!}</script>
    @endif
    <script type="application/ld+json">{!! json_encode($faqBreadcrumbSchema, $schemaFlags) !!}</script>

    <section class="blog art-section-pd art-faq-page">
        <div class="container">

            <div class="row">
                <header class="col-12 art-header-left">
                    <div>
                        <h1 class="title">{{ $faqTitle }}</h1>
                        <div class="subtitle font-two">
                            <p>{{ $faqIntro }}</p>
                        </div>
                    </div>
                </header>
            </div>

            @if($faqGroups->isEmpty())
                <div class="row">
                    <div class="col-12">
                        <p class="nothing-found-text mt-5">{{ trans('base.faq_empty') }}</p>
                    </div>
                </div>
            @else
                @foreach($faqGroups as $group)
                    <div class="row art-faq-group">
                        <div class="col-12">
                            <h2 class="art-faq-group__title">{{ $group['title'] }}</h2>

                            @foreach($group['items'] as $faq)
                                @continue(!trim((string) $faq->question) || !trim((string) $faq->answer))
                                <div class="accordion-item-wrapper">
                                    <h3 class="accordion{{ $loop->parent->first && $loop->first ? ' active' : '' }}" tabindex="0">
                                        <span class="question">{{ $faq->question }}</span>
                                    </h3>
                                    <div class="art-panel"@if($loop->parent->first && $loop->first) style="max-height: 2000px;"@endif>
                                        <div class="panel-data">{!! nl2br(e($faq->answer)) !!}</div>
                                    </div>
                                </div>
                            @endforeach

                            @if($group['url'])
                                <a class="art-faq-group__link" href="{{ $group['url'] }}">{{ trans('base.faq_group_link') }} &rarr;</a>
                            @endif
                        </div>
                    </div>
                @endforeach
            @endif

        </div>
    </section>

@endsection

@push('head')
    <style>
        .art-faq-page .art-faq-group {
            margin-top: 35px;
        }

        .art-faq-page .art-faq-group__title {
            margin-bottom: 18px;
            text-transform: none;
        }

        .art-faq-page .accordion {
            margin: 0;
            font-weight: 500;
            line-height: 1.35;
            text-transform: none;
        }

        .art-faq-page .art-faq-group__link {
            display: inline-block;
            margin-top: 10px;
            font-size: 14px;
            font-weight: 500;
        }
    </style>
@endpush

@push('dynamic_scripts')
    <script>
        // The trigger is a heading, so it needs the keyboard behaviour a
        // button would have brought with it.
        document.querySelectorAll('.art-faq-page .accordion[tabindex]').forEach(function (heading) {
            heading.addEventListener('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                    event.preventDefault();
                    heading.click();
                }
            });
        });
    </script>
@endpush
