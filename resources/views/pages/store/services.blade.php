@extends('layouts.store-main')

@php
    $servicesTitle = trans('base.services');
    $servicesDescriptionSource = $config->meta_description
        ?: optional($sections->first())->description;
    $servicesDescription = trim((string) preg_replace(
        '/\s+/u',
        ' ',
        html_entity_decode(strip_tags((string) $servicesDescriptionSource))
    ));
    $servicesLead = Illuminate\Support\Str::limit($servicesDescription, 240);
    $servicesPageTitle = $config->meta_title ?: $servicesTitle.' — '.trans('base.site_title');
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $servicesPageTitle)
@section('meta_description', $servicesDescription)
@section('meta_keywords', $config->meta_keywords ?: '')
@section('og_title', $servicesPageTitle)
@section('og_description', $servicesDescription)

@push('head')
    @if($config->meta_tags)
        {!! $config->meta_tags !!}
    @endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => url()->current().'#services-page',
        'url' => url()->current(),
        'name' => $servicesPageTitle,
        'description' => $servicesDescription ?: null,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'mainEntity' => $sections->isNotEmpty() ? [
            '@type' => 'ItemList',
            'itemListElement' => $sections->values()->map(fn ($section, $index) => [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => (string) $section->title,
                'url' => url()->current().'#service-'.$section->id,
            ])->all(),
        ] : null,
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $servicesTitle, 'item' => url()->current()],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-services-page">
        <x-store.content-breadcrumbs :items="[['label' => $servicesTitle]]" />

        <section class="bona-content-hero" aria-labelledby="services-page-title">
            <div class="bona-shell bona-content-hero__grid">
                <div class="bona-content-hero__copy">
                    <p class="bona-content-kicker">{{ trans('base.content_services_kicker') }}</p>
                    <h1 id="services-page-title">{{ $servicesTitle }}</h1>
                </div>
                @if($servicesLead)
                    <p class="bona-content-hero__lead">{{ $servicesLead }}</p>
                @endif
            </div>
        </section>

        <section class="bona-services-list" aria-labelledby="services-list-title">
            <div class="bona-shell">
                <header class="bona-content-heading">
                    <div>
                        <p class="bona-content-kicker">{{ trans('base.content_services_list_kicker') }}</p>
                        <h2 id="services-list-title">{{ trans('base.content_services_list_title') }}</h2>
                    </div>
                </header>

                @if($sections->isNotEmpty())
                    <div class="bona-services-list__items">
                        @foreach($sections as $section)
                            @php
                                $hasServiceImage = filled($section->section_image_path);
                            @endphp
                            <article class="bona-service-row{{ $hasServiceImage ? '' : ' bona-service-row--text-only' }}" id="service-{{ $section->id }}">
                                @if($hasServiceImage)
                                    <div class="bona-service-row__media">
                                        <img
                                            src="{{ $section->section_image_url }}"
                                            alt="{{ $section->title }}"
                                            width="720"
                                            height="540"
                                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                            decoding="async"
                                        >
                                    </div>
                                @endif
                                <div class="bona-service-row__copy">
                                    <span class="bona-service-row__number">{{ trans('base.content_service_number', ['number' => str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT)]) }}</span>
                                    <h2>{{ $section->title }}</h2>
                                    @if(filled(strip_tags((string) $section->description)))
                                        <div class="bona-content-richtext">{!! $section->description !!}</div>
                                    @endif
                                    @if($section->button_text)
                                        <div class="bona-content-inline-action">
                                            <a
                                                class="bona-button bona-button--dark"
                                                href="#dialog-call-measurer"
                                                data-lead-modal-open="dialog-call-measurer"
                                            >
                                                {{ $section->button_text }}
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @else
                    <div class="bona-content-empty"><p>{{ trans('base.services_empty') }}</p></div>
                @endif
            </div>
        </section>
    </div>
@endsection
