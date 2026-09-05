@php
    $homeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.home');
    $breadcrumbs = $breadcrumbs ?? [];
@endphp

<x-store.call-consultation-modal :options="$applicationGlobalOptions ?? []" />

<main class="bona-catalog">
    <div class="bona-shell">
        <nav class="bona-catalog__breadcrumbs" aria-label="{{ trans('base.breadcrumbs') }}">
            <ol itemscope itemtype="https://schema.org/BreadcrumbList">
                <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                    <a itemprop="item" href="{{ $homeUrl }}"><span itemprop="name">{{ trans('base.home') }}</span></a>
                    <meta itemprop="position" content="1">
                </li>
                @foreach($breadcrumbs as $breadcrumb)
                    <li itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem">
                        @if(filled($breadcrumb['url'] ?? null) && ! $loop->last)
                            <a itemprop="item" href="{{ $breadcrumb['url'] }}"><span itemprop="name">{{ $breadcrumb['label'] }}</span></a>
                        @else
                            <span itemprop="name" aria-current="{{ $loop->last ? 'page' : 'false' }}">{{ $breadcrumb['label'] }}</span>
                        @endif
                        <meta itemprop="position" content="{{ $loop->iteration + 1 }}">
                    </li>
                @endforeach
            </ol>
        </nav>

        <header class="bona-catalog__heading">
            <div>
                <p class="bona-kicker">{{ trans('base.catalog_kicker') }}</p>
                <h1>{{ $catalogPageTitle }}</h1>
            </div>
            <div class="bona-catalog__guidance">
                <span>{{ trans('base.catalog_guidance_eyebrow') }}</span>
                <h2>{{ trans('base.catalog_guidance_title') }}</h2>
                <p>{{ trans('base.catalog_guidance_text') }}</p>
                <a href="#dialog-call-consultation" data-lead-modal-open="dialog-call-consultation">
                    {{ trans('base.catalog_guidance_action') }} <span aria-hidden="true">→</span>
                </a>
            </div>
        </header>

        <div class="bona-catalog__layout">
            @include('pages.store.partials.catalog-toolbar')
            @include('pages.store.partials.catalog-filters')

            <section class="bona-catalog__results" aria-live="polite" aria-label="{{ trans('base.catalog_results') }}">
                @if($productsPaginated->isNotEmpty())
                    <div class="bona-catalog__grid art-product-list art-three-column" data-catalog-grid>
                        @foreach($productsPaginated as $product)
                            @include('pages.store.partials.product_item', ['product' => $product, 'baseCurrency' => $baseCurrency])

                            @php
                                $catalogProductPosition = ($productsPaginated->firstItem() ?? 1) + $loop->index;
                            @endphp

                            @if($catalogProductPosition % 5 === 0)
                                @include('pages.store.partials.catalog-consultant-card', ['consultantVisibility' => 'desktop'])
                            @endif

                            @if($catalogProductPosition % 6 === 0)
                                @include('pages.store.partials.catalog-consultant-card', ['consultantVisibility' => 'mobile'])
                            @endif
                        @endforeach
                    </div>

                    <p class="sr-only" aria-live="polite" data-catalog-load-status></p>
                    {{ $productsPaginated->links('pagination.store') }}
                @else
                    <div class="bona-catalog__empty">
                        <h2>{{ trans('base.catalog_empty_title') }}</h2>
                        <p>{{ trans('base.catalog_empty_text') }}</p>
                        <button class="filter-reset bona-button bona-button--dark" type="button">{{ trans('base.filter_reset') }}</button>
                    </div>
                @endif
            </section>
        </div>
    </div>
</main>
