@props([
    'productTypes',
])

@php
    $menuTypes = $productTypes->values();
@endphp

<div class="bona-mega" id="bona-catalog-menu" data-mega-menu>
    <div class="bona-shell bona-mega__inner">
        <div class="bona-mega__aside" role="tablist" aria-label="{{ trans('base.storefront_catalog') }}">
            @forelse($menuTypes as $productType)
                <button
                    class="bona-mega__tab{{ $loop->first ? ' is-active' : '' }}"
                    id="bona-mega-tab-{{ $productType->id }}"
                    type="button"
                    role="tab"
                    aria-controls="bona-mega-panel-{{ $productType->id }}"
                    aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                    tabindex="{{ $loop->first ? '0' : '-1' }}"
                    data-mega-tab="{{ $productType->id }}"
                >
                    {{ $productType->name }}
                </button>
            @empty
                <span class="bona-mega__empty">{{ trans('base.storefront_catalog_empty') }}</span>
            @endforelse

            <a class="bona-mega__all" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.all-products.page') }}">
                {{ trans('base.all_products') }}
                <span aria-hidden="true">→</span>
            </a>
        </div>

        <div class="bona-mega__panels">
            @foreach($menuTypes as $productType)
                @php
                    $categories = $productType->categories->values();
                    $typeUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                        'productTypeSlug' => $productType->slug,
                    ]);
                @endphp
                <section
                    class="bona-mega__panel{{ $loop->first ? ' is-active' : '' }}"
                    id="bona-mega-panel-{{ $productType->id }}"
                    role="tabpanel"
                    aria-labelledby="bona-mega-tab-{{ $productType->id }}"
                    data-mega-panel="{{ $productType->id }}"
                    @if(!$loop->first) hidden @endif
                >
                    <div class="bona-mega__heading">
                        <div>
                            <span>{{ trans('base.storefront_collection') }}</span>
                            <h2>{{ $productType->name }}</h2>
                        </div>
                        <a href="{{ $typeUrl }}">{{ trans('base.storefront_view_all') }} <span aria-hidden="true">→</span></a>
                    </div>

                    @if($categories->isNotEmpty())
                        <div class="bona-mega__cards">
                            @foreach($categories->take(5) as $category)
                                <a class="bona-mega-card" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                                    'productTypeSlug' => $productType->slug,
                                    'categorySlug' => $category->slug,
                                ]) }}">
                                    <span class="bona-mega-card__image">
                                        @if($category->image_url)
                                            <img src="{{ $category->image_url }}" alt="" loading="lazy">
                                        @else
                                            <span aria-hidden="true">BONA</span>
                                        @endif
                                    </span>
                                    <span>{{ $category->name }}</span>
                                </a>
                            @endforeach
                        </div>

                        @if($categories->count() > 5)
                            <div class="bona-mega__links" aria-label="{{ trans('base.storefront_more_categories') }}">
                                @foreach($categories->skip(5)->take(12) as $category)
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                                        'productTypeSlug' => $productType->slug,
                                        'categorySlug' => $category->slug,
                                    ]) }}">{{ $category->name }}</a>
                                @endforeach
                            </div>
                        @endif
                    @else
                        <a class="bona-mega__type-card" href="{{ $typeUrl }}">
                            @if($productType->image_url)
                                <img src="{{ $productType->image_url }}" alt="" loading="lazy">
                            @endif
                            <span>
                                <small>{{ trans('base.storefront_open_catalog') }}</small>
                                <strong>{{ $productType->name }}</strong>
                            </span>
                            <b aria-hidden="true">→</b>
                        </a>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
</div>
