@php
    $googleAnalyticsId = (string) config('services.google.analytics_id', '');
@endphp

<aside
    class="bona-cookie-consent"
    data-cookie-consent
    data-google-analytics-id="{{ $googleAnalyticsId }}"
    aria-labelledby="bona-cookie-consent-title"
    hidden
>
    <div class="bona-cookie-consent__copy">
        <strong id="bona-cookie-consent-title">{{ trans('base.cookie_title') }}</strong>
        <p>{{ trans('base.cookie_text') }}</p>
        <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.static-page.page', ['staticPageSlug' => 'polityka-konfidencinosti']) }}">
            {{ trans('base.cookie_policy_link') }}
        </a>
    </div>

    <div class="bona-cookie-consent__actions">
        <button type="button" class="bona-cookie-consent__secondary" data-cookie-consent-necessary>
            {{ trans('base.cookie_necessary_only') }}
        </button>
        <button type="button" class="bona-cookie-consent__primary" data-cookie-consent-accept>
            {{ trans('base.cookie_accept_all') }}
        </button>
    </div>
</aside>
