@extends('layouts.store-main')

@php
    $isRussian = app()->getLocale() === 'ru';
    $title = $isRussian ? 'Конфигуратор дверей' : 'Конфігуратор дверей';
    $description = $isRussian
        ? 'Подбор дверей Bona Doors по стилю интерьера, цвету, назначению и бюджету.'
        : 'Підбір дверей Bona Doors за стилем інтер’єру, кольором, призначенням і бюджетом.';
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $title.' — '.trans('base.site_title'))
@section('meta_description', $description)
@section('og_title', $title.' — '.trans('base.site_title'))
@section('og_description', $description)

@push('structured_data')
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url()->current().'#webpage',
        'url' => url()->current(),
        'name' => $title,
        'description' => $description,
        'inLanguage' => $isRussian ? 'ru-UA' : 'uk-UA',
    ], $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $title, 'item' => url()->current()],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <main class="bona-door-configurator">
        <x-store.content-breadcrumbs :items="[['label' => $title]]" />

        <section class="bona-door-configurator__hero" aria-labelledby="door-configurator-title">
            <div class="bona-shell bona-door-configurator__hero-grid">
                <div>
                    <p class="bona-content-kicker">Bona Doors · {{ $isRussian ? 'Подбор модели' : 'Підбір моделі' }}</p>
                    <h1 id="door-configurator-title">{{ $isRussian ? 'Двери, которые подходят вашему пространству' : 'Двері, що пасують вашому простору' }}</h1>
                    <p>{{ $isRussian ? 'Полноценный интерактивный подбор уже готовится. А сейчас наш менеджер пройдёт с вами тот же путь лично: уточнит интерьер, размеры и бюджет и предложит совместимые модели.' : 'Повноцінний інтерактивний підбір уже готується. А зараз наш менеджер пройде з вами той самий шлях особисто: уточнить інтер’єр, розміри й бюджет та запропонує сумісні моделі.' }}</p>
                    <button class="bona-button bona-button--dark" type="button" data-lead-modal-open="dialog-call-consultation">{{ $isRussian ? 'Начать подбор с менеджером' : 'Почати підбір з менеджером' }} <span aria-hidden="true">→</span></button>
                </div>
                <figure>
                    <img src="{{ Vite::asset('bona-html/img/interior-bedroom.jpg') }}" alt="{{ $isRussian ? 'Двери в современном интерьере' : 'Двері в сучасному інтер’єрі' }}" width="760" height="760" fetchpriority="high" decoding="async">
                    <figcaption><span>01</span>{{ $isRussian ? 'Стиль · цвет · бюджет' : 'Стиль · колір · бюджет' }}</figcaption>
                </figure>
            </div>
        </section>

        <section class="bona-door-configurator__steps" aria-labelledby="configurator-steps-title">
            <div class="bona-shell">
                <header><p class="bona-content-kicker">{{ $isRussian ? 'Три понятных шага' : 'Три зрозумілі кроки' }}</p><h2 id="configurator-steps-title">{{ $isRussian ? 'Что понадобится для точного подбора' : 'Що знадобиться для точного підбору' }}</h2></header>
                <div>
                    <article><span>01</span><h3>{{ $isRussian ? 'Фото интерьера' : 'Фото інтер’єру' }}</h3><p>{{ $isRussian ? 'Покажите стены, пол и мебель — так мы точнее подберём оттенок и стиль.' : 'Покажіть стіни, підлогу й меблі — так ми точніше підберемо відтінок і стиль.' }}</p></article>
                    <article><span>02</span><h3>{{ $isRussian ? 'Размеры проёма' : 'Розміри прорізу' }}</h3><p>{{ $isRussian ? 'Если замеров ещё нет, подскажем, как их снять, или договоримся о выезде мастера.' : 'Якщо замірів ще немає, підкажемо, як їх зняти, або домовимося про виїзд майстра.' }}</p></article>
                    <article><span>03</span><h3>{{ $isRussian ? 'Ориентир по бюджету' : 'Орієнтир за бюджетом' }}</h3><p>{{ $isRussian ? 'Сразу считаем не только полотно, а совместимый комплект и доступные варианты оплаты.' : 'Одразу рахуємо не лише полотно, а сумісний комплект і доступні варіанти оплати.' }}</p></article>
                </div>
            </div>
        </section>
    </main>

    <x-store.call-consultation-modal :options="$applicationGlobalOptions ?? []" />
@endsection
