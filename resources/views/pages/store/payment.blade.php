@extends('layouts.store-main')

@section('body_class', 'bona-commerce-body')
@section('seo_title', trans('base.liqpay_page_title').' — '.config('app.name'))
@section('meta_description', trans('base.liqpay_page_intro'))

@push('head')
    <meta name="robots" content="noindex, nofollow">
@endpush

@section('content')
    <div class="bona-commerce-page bona-liqpay-page">
        <x-store.content-breadcrumbs :items="[['label' => trans('base.liqpay_page_title')]]" />

        <section class="bona-liqpay-page__section" aria-labelledby="liqpay-page-title">
            <div class="bona-shell bona-liqpay-page__layout">
                <header class="bona-liqpay-page__intro">
                    <p class="bona-commerce-kicker">{{ trans('base.liqpay_page_kicker') }}</p>
                    <h1 id="liqpay-page-title">{{ trans('base.liqpay_page_title') }}</h1>
                    <p>{{ trans('base.liqpay_page_intro') }}</p>

                    <div class="bona-liqpay-page__order">
                        <span>{{ trans('base.liqpay_page_order') }}</span>
                        <strong>#BD-{{ str_pad((string) $order->id, 6, '0', STR_PAD_LEFT) }}</strong>
                    </div>

                    <p class="bona-liqpay-page__security">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7.5 10V7.5a4.5 4.5 0 0 1 9 0V10m-10 0h11a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1h-11a1 1 0 0 1-1-1v-8a1 1 0 0 1 1-1Z" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 14v2" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
                        <span>{{ trans('base.liqpay_page_secure') }}</span>
                    </p>
                </header>

                <div class="bona-liqpay-card" data-liqpay-card>
                    <div class="bona-liqpay-card__loading" data-liqpay-loading role="status">
                        <span aria-hidden="true"></span>
                        <p>{{ trans('base.liqpay_loading') }}</p>
                    </div>
                    <div id="liqpay_checkout"></div>
                    <noscript>
                        <style>[data-liqpay-loading] { display: none !important; }</style>
                        <p class="bona-liqpay-card__error">{{ trans('base.liqpay_javascript_required') }}</p>
                    </noscript>
                </div>
            </div>
        </section>
    </div>

    <script>
        window.BonaLiqPayFailed = function() {
            const loading = document.querySelector('[data-liqpay-loading]');
            if (!loading) return;
            loading.classList.add('is-error');
            loading.querySelector('p').textContent = @json(trans('base.liqpay_load_error'));
        };

        window.LiqPayCheckoutCallback = function() {
            LiqPayCheckout.init({
                data: @json($data),
                signature: @json($signature),
                embedTo: '#liqpay_checkout',
                language: @json(app()->getLocale()),
                mode: 'embed'
            }).on('liqpay.callback', function() {
                // Payment state is changed only by LiqPay's signed server callback.
            }).on('liqpay.ready', function() {
                document.querySelector('[data-liqpay-card]')?.classList.add('is-ready');
                const loading = document.querySelector('[data-liqpay-loading]');
                if (loading) loading.hidden = true;
            });
        };
    </script>
    <script src="https://static.liqpay.ua/libjs/checkout.js" async onerror="window.BonaLiqPayFailed()"></script>
@endsection
