@props([
    'title',
    'kicker' => null,
    'intro' => null,
    'state' => false,
])

<div class="bona-auth-page bona-content-page">
    <x-store.content-breadcrumbs :items="[['label' => $title]]" />

    <section class="bona-auth-section" aria-labelledby="bona-auth-title">
        <div class="bona-shell bona-auth-section__layout">
            <aside class="bona-auth-intro" aria-label="{{ trans('auth.portal_title') }}">
                <div>
                    <p class="bona-content-kicker">{{ trans('auth.portal_kicker') }}</p>
                    <h2>{{ trans('auth.portal_title') }}</h2>
                    <p>{{ trans('auth.portal_intro') }}</p>
                </div>

                <ul>
                    <li>
                        <span aria-hidden="true">01</span>
                        {{ trans('auth.portal_benefit_orders') }}
                    </li>
                    <li>
                        <span aria-hidden="true">02</span>
                        {{ trans('auth.portal_benefit_checkout') }}
                    </li>
                    <li>
                        <span aria-hidden="true">03</span>
                        {{ trans('auth.portal_benefit_saved') }}
                    </li>
                </ul>
            </aside>

            <div class="bona-auth-card{{ $state ? ' bona-auth-card--state' : '' }}">
                <header class="bona-auth-card__header">
                    <p class="bona-content-kicker">{{ $kicker ?: trans('auth.account_kicker') }}</p>
                    <h1 id="bona-auth-title">{{ $title }}</h1>
                    @if($intro)
                        <p>{{ $intro }}</p>
                    @endif
                </header>

                {{ $slot }}
            </div>
        </div>
    </section>
</div>
