@extends('layouts.store-main')

@php
    $comparisonBaseUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.comparison.page');
    $catalogUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page');
    $selectedSlugs = $products->pluck('slug')->values();
@endphp

@section('title')
    <title>{{ trans('base.comparison_title') }} — {{ config('app.name') }}</title>
    <meta name="description" content="{{ trans('base.comparison_intro') }}">
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
    <main
        class="bona-comparison {{ $products->isEmpty() ? 'bona-comparison--empty' : '' }}"
        data-comparison-page
        data-comparison-base-url="{{ $comparisonBaseUrl }}"
        data-comparison-selected="{{ $selectedSlugs->toJson() }}"
        data-comparison-has-query="{{ $hasProductsQuery ? 'true' : 'false' }}"
    >
        <div class="bona-shell">
            <nav class="bona-comparison__breadcrumbs" aria-label="{{ trans('base.breadcrumbs') }}">
                <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">{{ trans('base.go_to_main') }}</a>
                <span aria-hidden="true">/</span>
                <span aria-current="page">{{ trans('base.comparison') }}</span>
            </nav>

            <header class="bona-comparison__heading">
                <div>
                    <p class="bona-kicker">{{ trans('base.comparison_kicker') }}</p>
                    <h1>{{ trans('base.comparison_title') }}</h1>
                    <p>{{ trans('base.comparison_intro') }}</p>
                </div>
                @if($products->isNotEmpty())
                    <div class="bona-comparison__selection" aria-label="{{ trans('base.comparison_selected') }}">
                        <strong>{{ $products->count() }}</strong>
                        <span>{{ trans('base.comparison_selected') }} / {{ $maxProducts }}</span>
                    </div>
                @endif
            </header>

            @if($products->isEmpty())
                <section class="bona-comparison__empty" aria-labelledby="comparison-empty-title">
                    <div class="bona-comparison__empty-icon" aria-hidden="true">
                        <svg viewBox="0 0 40 40" fill="none">
                            <path d="M7 13h25M27 8l5 5-5 5M33 27H8M13 22l-5 5 5 5"></path>
                        </svg>
                    </div>
                    <h2 id="comparison-empty-title">{{ trans('base.comparison_empty_title') }}</h2>
                    <p>{{ trans('base.comparison_empty_text') }}</p>
                    <a class="bona-button bona-button--dark" href="{{ $catalogUrl }}">
                        {{ trans('base.comparison_go_to_catalog') }}
                    </a>
                </section>
            @else
                <div class="bona-comparison__toolbar">
                    <div>
                        @if($products->count() < 2)
                            <p class="bona-comparison__hint" role="status">{{ trans('base.comparison_need_two') }}</p>
                        @else
                            <label class="bona-comparison__differences">
                                <input type="checkbox" data-comparison-differences>
                                <span aria-hidden="true"></span>
                                {{ trans('base.comparison_show_differences') }}
                            </label>
                        @endif
                    </div>
                    <div class="bona-comparison__toolbar-actions">
                        @if($products->count() < $maxProducts)
                            <a href="{{ $catalogUrl }}" class="bona-outline-link">{{ trans('base.comparison_add_more') }}</a>
                        @endif
                        <button type="button" class="bona-comparison__clear" data-comparison-clear>
                            {{ trans('base.comparison_clear') }}
                        </button>
                    </div>
                </div>

                <div
                    class="bona-comparison__table-shell"
                    role="region"
                    aria-label="{{ trans('base.comparison_table_label') }}"
                    tabindex="0"
                >
                    <table class="bona-comparison__table" style="--comparison-products: {{ $products->count() }}">
                        <caption class="sr-only">{{ trans('base.comparison_table_label') }}</caption>
                        <thead>
                            <tr>
                                <th class="bona-comparison__label-column" scope="col">
                                    <span>{{ trans('base.comparison_characteristic') }}</span>
                                </th>
                                @foreach($products as $product)
                                    @php
                                        $productUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $product->slug]);
                                        $imageUrl = $product->main_image_url ?: $product->preview_image_url;
                                        $status = $product->availability_status_id === App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_NONE
                                            ? null
                                            : data_get(App\DataClasses\ProductStatusDataClass::get($product->availability_status_id), 'name');
                                    @endphp
                                    <th class="bona-comparison__product-column" scope="col">
                                        <article class="bona-comparison-product">
                                            <button
                                                type="button"
                                                class="bona-comparison-product__remove"
                                                aria-label="{{ trans('base.comparison_remove_product', ['product' => $product->name]) }}"
                                                data-comparison-remove
                                                data-product-slug="{{ $product->slug }}"
                                            >
                                                <svg viewBox="0 0 18 18" fill="none" aria-hidden="true"><path d="m4 4 10 10M14 4 4 14"></path></svg>
                                            </button>
                                            <a class="bona-comparison-product__image" href="{{ $productUrl }}">
                                                <img src="{{ $imageUrl }}" alt="{{ $product->name }}" loading="lazy">
                                            </a>
                                            <div class="bona-comparison-product__body">
                                                <p>{{ collect([$product->brand?->name, $status])->filter()->join(' · ') }}</p>
                                                <h2><a href="{{ $productUrl }}">{{ $product->name }}</a></h2>
                                                @if($product->price > 0)
                                                    <strong>{{ number_format($product->price, 0, ',', ' ') }} {{ $baseCurrency->name_short }}</strong>
                                                @endif
                                                <a class="bona-comparison-product__link" href="{{ $productUrl }}">
                                                    {{ trans('base.comparison_view_product') }} <span aria-hidden="true">→</span>
                                                </a>
                                            </div>
                                        </article>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($comparisonRows as $row)
                                <tr data-comparison-row data-comparison-different="{{ $row['different'] ? 'true' : 'false' }}">
                                    <th scope="row">{{ $row['label'] }}</th>
                                    @foreach($products as $product)
                                        @php $value = $row['values']->get($product->slug); @endphp
                                        <td class="{{ $value ? '' : 'is-empty' }}">
                                            {{ $value ?: trans('base.comparison_no_value') }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </main>
@endsection
