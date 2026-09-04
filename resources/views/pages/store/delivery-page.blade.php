@extends('layouts.store-main')

@php
    $deliveryTitle = trans('base.delivery');
    $deliveryDescription = trim((string) ($deliveryConfig->meta_description ?: preg_replace(
        '/\s+/u',
        ' ',
        html_entity_decode(strip_tags((string) $deliveryConfig->description))
    )));
    $deliveryLead = Illuminate\Support\Str::limit($deliveryDescription, 240);
    $deliveryPageTitle = $deliveryConfig->meta_title ?: $deliveryTitle.' — '.trans('base.site_title');
    $hasDeliveryMedia = filled($deliveryConfig->iframe) || filled($deliveryConfig->image);
    $hasDeliveryContent = $hasDeliveryMedia
        || filled($deliveryConfig->title)
        || filled(strip_tags((string) $deliveryConfig->description))
        || (filled($deliveryConfig->button_url) && filled($deliveryConfig->button_text));
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $deliveryPageTitle)
@section('meta_description', $deliveryDescription)
@section('meta_keywords', $deliveryConfig->meta_keywords ?: '')
@section('og_title', $deliveryPageTitle)
@section('og_description', $deliveryDescription)

@if($deliveryConfig->image)
    @section('og_image', App\Helpers\PreviewImage::url($deliveryConfig->image))
@endif

@push('head')
    @if($deliveryConfig->meta_tags)
        {!! $deliveryConfig->meta_tags !!}
    @endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@'.'context' => 'https://schema.org',
        '@type' => 'WebPage',
        '@id' => url()->current().'#delivery-page',
        'url' => url()->current(),
        'name' => $deliveryPageTitle,
        'description' => $deliveryDescription ?: null,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'isPartOf' => ['@id' => app(App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url($homeUrl)],
            ['@type' => 'ListItem', 'position' => 2, 'name' => $deliveryTitle, 'item' => url()->current()],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-content-page bona-delivery-page">
        <x-store.content-breadcrumbs :items="[['label' => $deliveryTitle]]" />

        <section class="bona-content-hero" aria-labelledby="delivery-page-title">
            <div class="bona-shell bona-content-hero__grid">
                <div class="bona-content-hero__copy">
                    <p class="bona-content-kicker">{{ trans('base.content_delivery_kicker') }}</p>
                    <h1 id="delivery-page-title">{{ $deliveryTitle }}</h1>
                </div>
                @if($deliveryLead)
                    <p class="bona-content-hero__lead">{{ $deliveryLead }}</p>
                @endif
            </div>
        </section>

        @if($hasDeliveryContent)
            <section class="bona-content-feature bona-content-feature--reverse{{ $hasDeliveryMedia ? '' : ' bona-content-feature--text-only' }}">
                <div class="bona-shell bona-content-feature__grid">
                    @if($hasDeliveryMedia)
                        <div class="bona-content-feature__media">
                            @if(filled($deliveryConfig->iframe))
                                {!! $deliveryConfig->iframe !!}
                            @elseif($deliveryConfig->image)
                                <img
                                    src="{{ $deliveryConfig->imageUrl }}"
                                    alt="{{ $deliveryConfig->title ?: $deliveryTitle }}"
                                    width="760"
                                    height="850"
                                    decoding="async"
                                >
                            @endif
                        </div>
                    @endif

                    <div class="bona-content-feature__copy">
                        @if($deliveryConfig->title)
                            <p class="bona-content-kicker">{{ trans('base.content_delivery_details_kicker') }}</p>
                            <h2>{{ $deliveryConfig->title }}</h2>
                        @endif
                        @if(filled(strip_tags((string) $deliveryConfig->description)))
                            <div class="bona-content-richtext">{!! $deliveryConfig->description !!}</div>
                        @endif
                        @if($deliveryConfig->button_url && $deliveryConfig->button_text)
                            @php
                                $deliveryButtonExternal = Illuminate\Support\Str::startsWith($deliveryConfig->button_url, ['http://', 'https://']);
                            @endphp
                            <div class="bona-content-inline-action">
                                <a
                                    class="bona-button bona-button--dark"
                                    href="{{ $deliveryConfig->button_url }}"
                                    @if($deliveryButtonExternal) target="_blank" rel="noopener noreferrer" @endif
                                >
                                    {{ $deliveryConfig->button_text }}
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @else
            <section class="bona-services-list">
                <div class="bona-shell">
                    <div class="bona-content-empty"><p>{{ trans('base.delivery_empty') }}</p></div>
                </div>
            </section>
        @endif
    </div>
@endsection
