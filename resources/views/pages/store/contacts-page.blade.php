@extends('layouts.store-main')

@php
    $stores = App\Support\Storefront\StoreLocations::from($contactsConfig);
    $primaryStore = $stores->first();
    $primaryPhone = data_get($primaryStore, 'phone');
    $primaryPhoneHref = data_get($primaryStore, 'phone_href');
    $primaryEmail = data_get($primaryStore, 'email');
    $telegramUrl = data_get($applicationGlobalOptions, 'telegram');
    $pageTitle = $contactsConfig?->meta_title ?: trans('base.contacts').' - '.trans('base.site_title');
    $pageDescription = $contactsConfig?->meta_description ?: trans('base.contact_hero_intro');
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-contact-body')
@section('seo_title', $pageTitle)
@section('meta_description', $pageDescription)
@section('meta_keywords', $contactsConfig?->meta_keywords ?: '')
@section('og_title', $pageTitle)
@section('og_description', $pageDescription)

@push('head')
    @if($contactsConfig?->meta_tags)
        {!! $contactsConfig->meta_tags !!}
    @endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'ContactPage',
        '@id' => url()->current().'#contact-page',
        'url' => url()->current(),
        'name' => $pageTitle,
        'description' => $pageDescription,
        'inLanguage' => app()->getLocale() === 'ru' ? 'ru-UA' : 'uk-UA',
        'mainEntity' => ['@id' => app(App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
    ], $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@'.'context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => trans('base.home'),
                'item' => App\Helpers\MultiLangRoute::getMultiLangRoute('store.home'),
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => trans('base.contacts'),
                'item' => url()->current(),
            ],
        ],
    ], $schemaFlags) !!}</script>
@endpush

@section('content')
    <div class="bona-contact-page">
        <nav class="bona-contact-breadcrumbs bona-shell" aria-label="{{ trans('base.breadcrumbs') }}">
            <ol>
                <li><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">{{ trans('base.home') }}</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page">{{ trans('base.contacts') }}</li>
            </ol>
        </nav>

        <section class="bona-contact-hero" aria-labelledby="contact-page-title">
            <div class="bona-shell bona-contact-hero__grid">
                <div class="bona-contact-hero__copy">
                    <p class="bona-contact-kicker">{{ trans('base.contact_hero_kicker') }}</p>
                    <h1 id="contact-page-title">{{ trans('base.contact_hero_title') }}</h1>
                </div>
                <div>
                    <p class="bona-contact-hero__lead">{{ trans('base.contact_hero_intro') }}</p>
                    <div class="bona-contact-hero__actions">
                        <a class="bona-button bona-button--dark" href="#contact-measure">
                            {{ trans('base.contact_measure_cta') }}
                        </a>
                        @if($primaryPhone && $primaryPhoneHref)
                            <a class="bona-contact-button-secondary" href="tel:{{ $primaryPhoneHref }}">
                                {{ trans('base.contact_call') }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @if($stores->isNotEmpty())
            <section class="bona-contact-showrooms" aria-labelledby="contact-showrooms-title">
                <div class="bona-shell">
                    <div class="bona-contact-section-head">
                        <div>
                            <p class="bona-contact-kicker">{{ trans('base.contact_showrooms_kicker') }}</p>
                            <h2 id="contact-showrooms-title">{{ trans('base.contact_showrooms_title') }}</h2>
                        </div>
                        <p>{{ trans('base.contact_showrooms_intro') }}</p>
                    </div>

                    <div class="bona-contact-layout">
                        <div class="bona-contact-showroom-list">
                            @foreach($stores as $store)
                                <article class="bona-contact-showroom-card">
                                    <div>
                                        <h3>{{ $store['name'] }}</h3>
                                        <p>
                                            <a href="{{ $store['map_url'] }}" target="_blank" rel="noopener noreferrer">
                                                {{ $store['address'] }}
                                            </a>
                                            <span>{{ $store['working_hours'] }}</span>
                                        </p>
                                    </div>
                                    <div class="bona-contact-showroom-card__meta">
                                        @if($store['phone'] && $store['phone_href'])
                                            <a href="tel:{{ $store['phone_href'] }}">{{ $store['phone'] }}</a>
                                        @endif
                                        <a class="bona-contact-route" href="{{ $store['map_url'] }}" target="_blank" rel="noopener noreferrer">
                                            {{ trans('base.contact_route') }}
                                            <span aria-hidden="true">↗</span>
                                        </a>
                                    </div>
                                </article>
                            @endforeach
                        </div>

                        <div class="bona-contact-maps" aria-label="{{ trans('base.contact_maps_label') }}">
                            @foreach($stores as $store)
                                <div class="bona-contact-map">
                                    @if($store['iframe_html'])
                                        {!! $store['iframe_html'] !!}
                                    @endif
                                    <a href="{{ $store['map_url'] }}" target="_blank" rel="noopener noreferrer">
                                        <strong>{{ $store['name'] }}</strong>
                                        <span>{{ $store['address'] }} ↗</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bona-contact-channels">
                        @if($primaryPhone && $primaryPhoneHref)
                            <div class="bona-contact-channel">
                                <small>{{ trans('base.contact_channel_phone') }}</small>
                                <a href="tel:{{ $primaryPhoneHref }}">{{ $primaryPhone }}</a>
                            </div>
                        @endif
                        @if($primaryEmail)
                            <div class="bona-contact-channel">
                                <small>{{ trans('base.contact_channel_write') }}</small>
                                <a href="mailto:{{ $primaryEmail }}">{{ $primaryEmail }}</a>
                            </div>
                        @endif
                        @if($telegramUrl)
                            <div class="bona-contact-channel">
                                <small>{{ trans('base.contact_channel_messenger') }}</small>
                                <a href="{{ $telegramUrl }}" target="_blank" rel="noopener noreferrer">Telegram Bona</a>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        <section class="bona-contact-measure" id="contact-measure" aria-labelledby="contact-measure-title">
            <div class="bona-shell">
                <div class="bona-contact-form-shell" data-lead-inline>
                    <div class="bona-contact-form-shell__intro">
                        <p class="bona-contact-kicker">{{ trans('base.lead_measurer_kicker') }}</p>
                        <h2 id="contact-measure-title">{{ trans('base.lead_measurer_title') }}</h2>
                        <p>{{ trans('base.contact_measure_intro') }}</p>
                    </div>

                    <div class="bona-contact-form-view" data-lead-inline-form-view>
                        <form
                            class="bona-lead-form"
                            action="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.choose.doors') }}"
                            method="post"
                            data-lead-form="measure"
                            data-sending-label="{{ trans('base.lead_sending') }}"
                            data-error-label="{{ trans('base.lead_submit_error') }}"
                            data-phone-error="{{ trans('base.lead_phone_invalid') }}"
                        >
                            @csrf
                            <input type="hidden" name="title" value="{{ trans('base.call_measurer') }}">
                            <input type="hidden" name="event" value="submit_form_contacts_measure">

                            <div class="bona-lead-form__grid">
                                <label class="bona-lead-field">
                                    <span>{{ trans('base.name') }}</span>
                                    <input type="text" name="name" autocomplete="name" minlength="2" maxlength="120" required placeholder="{{ trans('base.lead_name_placeholder') }}">
                                </label>
                                <label class="bona-lead-field">
                                    <span>{{ trans('base.phone') }}</span>
                                    <input class="js-ua-phone" type="tel" name="phone" autocomplete="tel" inputmode="tel" required placeholder="+38 (0__) ___ __ __">
                                </label>
                                <label class="bona-lead-field bona-lead-field--wide">
                                    <span>{{ trans('base.your_message') }}</span>
                                    <textarea name="description" rows="4" maxlength="2000" placeholder="{{ trans('base.lead_measurer_message_placeholder') }}"></textarea>
                                </label>
                            </div>

                            <label class="bona-lead-consent">
                                <input type="checkbox" name="agree" value="1" required>
                                <span class="bona-lead-consent__box" aria-hidden="true"></span>
                                <span>
                                    {{ trans('base.agreement_line_start') }}
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'dogovir-publichnoyi-oferti']) }}">
                                        {{ trans('base.agreement_line_end') }}
                                    </a>
                                </span>
                            </label>

                            <label class="bona-lead-form__trap" aria-hidden="true">
                                Website <input type="text" name="website" tabindex="-1" autocomplete="off">
                            </label>

                            <p class="bona-lead-form__error" data-lead-form-error role="alert" hidden></p>
                            <button class="bona-lead-form__submit" type="submit" data-submit-label="{{ trans('base.lead_measurer_submit') }}">
                                <span>{{ trans('base.lead_measurer_submit') }}</span>
                                <svg viewBox="0 0 24 12" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M1 6h21M17 1l5 5-5 5"></path>
                                </svg>
                            </button>
                        </form>
                    </div>

                    <div class="bona-contact-form-thanks" data-lead-inline-thanks hidden tabindex="-1">
                        <span aria-hidden="true">✓</span>
                        <p class="bona-contact-kicker">{{ trans('base.lead_measurer_success_kicker') }}</p>
                        <h2>{{ trans('base.lead_measurer_success_title') }}</h2>
                        <p>{{ trans('base.lead_measurer_success_text') }}</p>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
