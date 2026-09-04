@extends('layouts.store-main')

@php
    $plainDescription = trim((string) preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags(
        (string) ($service->meta_description ?: $service->intro ?: $service->description)
    ))));
    $pageTitle = $service->meta_title ?: $service->title.' — '.trans('base.site_title');
    $servicesUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.services');
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $serviceCtaUrl = filled($service->button_url) ? trim((string) $service->button_url) : '#dialog-call-measurer';
    $serviceCtaModal = str_starts_with($serviceCtaUrl, '#dialog-') ? ltrim($serviceCtaUrl, '#') : null;
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $pageTitle)
@section('meta_description', $plainDescription)
@section('meta_keywords', $service->meta_keywords ?: '')
@section('og_title', $pageTitle)
@section('og_description', $plainDescription)
@if($service->section_image_url)
    @section('og_image', $service->section_image_url)
    @section('og_image_alt', $service->title)
@endif

@push('head')
    @if($service->meta_tags)
        {!! $service->meta_tags !!}
    @endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'Service',
        '@id' => url()->current().'#service',
        'url' => url()->current(),
        'name' => (string) $service->title,
        'description' => $plainDescription ?: null,
        'image' => $service->section_image_url ?: null,
        'provider' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        'areaServed' => ['@type' => 'City', 'name' => app()->getLocale() === 'ru' ? 'Одесса' : 'Одеса'],
        'serviceType' => (string) $service->title,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => trans('base.services'), 'item' => url($servicesUrl)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $service->title, 'item' => url()->current()],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-service-detail">
        <x-store.content-breadcrumbs :items="[
            ['label' => trans('base.services'), 'url' => $servicesUrl],
            ['label' => $service->title],
        ]" />

        <section class="bona-service-detail__hero" aria-labelledby="service-title">
            <div class="bona-shell bona-service-detail__hero-grid">
                <div class="bona-service-detail__hero-copy">
                    <p class="bona-content-kicker">{{ app()->getLocale() === 'ru' ? 'Сервис Bona Doors' : 'Сервіс Bona Doors' }}</p>
                    <h1 id="service-title">{{ $service->title }}</h1>
                    @if(filled(strip_tags((string) ($service->intro ?: $service->description))))
                        <div class="bona-service-detail__lead">
                            {!! $service->intro ?: $service->description !!}
                        </div>
                    @endif
                    <a class="bona-button bona-button--dark" href="{{ $serviceCtaUrl }}" @if($serviceCtaModal) data-lead-modal-open="{{ $serviceCtaModal }}" @endif>
                        {{ $service->button_text ?: (app()->getLocale() === 'ru' ? 'Получить консультацию' : 'Отримати консультацію') }}
                    </a>
                </div>

                @if($service->section_image_url)
                    <figure class="bona-service-detail__hero-media">
                        <img src="{{ $service->section_image_url }}" alt="{{ $service->title }}" width="960" height="720" fetchpriority="high" decoding="async">
                    </figure>
                @endif
            </div>
        </section>

        @if(filled(strip_tags((string) $service->content)))
            <section class="bona-service-detail__content">
                <div class="bona-shell bona-service-detail__content-grid">
                    <div class="bona-service-detail__aside">
                        <span>01</span>
                        <p>{{ app()->getLocale() === 'ru' ? 'Что входит в услугу' : 'Що входить у послугу' }}</p>
                    </div>
                    <div class="bona-content-richtext bona-service-detail__richtext">
                        {!! $service->content !!}
                    </div>
                </div>
            </section>
        @endif

        <section class="bona-service-detail__cta">
            <div class="bona-shell">
                <div class="bona-service-detail__cta-panel">
                    <div>
                        <p class="bona-content-kicker">{{ app()->getLocale() === 'ru' ? 'Следующий шаг' : 'Наступний крок' }}</p>
                        <h2>{{ app()->getLocale() === 'ru' ? 'Обсудим вашу задачу и предложим точное решение' : 'Обговоримо ваше завдання й запропонуємо точне рішення' }}</h2>
                    </div>
                    <a class="bona-button bona-button--light" href="{{ $serviceCtaUrl }}" @if($serviceCtaModal) data-lead-modal-open="{{ $serviceCtaModal }}" @endif>
                        {{ app()->getLocale() === 'ru' ? 'Связаться с менеджером' : 'Зв’язатися з менеджером' }}
                    </a>
                </div>
            </div>
        </section>

        @if($otherServices->isNotEmpty())
            <section class="bona-service-detail__related" aria-labelledby="other-services-title">
                <div class="bona-shell">
                    <header class="bona-content-heading">
                        <div>
                            <p class="bona-content-kicker">{{ app()->getLocale() === 'ru' ? 'Другие услуги' : 'Інші послуги' }}</p>
                            <h2 id="other-services-title">{{ app()->getLocale() === 'ru' ? 'Можем помочь еще' : 'Можемо допомогти ще' }}</h2>
                        </div>
                    </header>
                    <div class="bona-service-detail__related-grid">
                        @foreach($otherServices as $otherService)
                            <article class="bona-service-mini-card">
                                @if($otherService->section_image_url)
                                    <a class="bona-service-mini-card__media" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.service.page', ['serviceSlug' => $otherService->slug]) }}">
                                        <img src="{{ $otherService->section_image_url }}" alt="{{ $otherService->title }}" width="600" height="450" loading="lazy" decoding="async">
                                    </a>
                                @endif
                                <div>
                                    <h3><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.service.page', ['serviceSlug' => $otherService->slug]) }}">{{ $otherService->title }}</a></h3>
                                    <a class="bona-service-mini-card__link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.service.page', ['serviceSlug' => $otherService->slug]) }}">
                                        {{ app()->getLocale() === 'ru' ? 'Подробнее' : 'Докладніше' }} <span aria-hidden="true">→</span>
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    </div>
@endsection
