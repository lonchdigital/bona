@php
    $isRussian = app()->getLocale() === 'ru';
    $isWishlisted = collect($wishListProducts ?? [])->contains($product->id);
    $hasPrice = is_numeric($product->price) && (float) $product->price > 0;
    $canPurchase = $hasPrice && ! in_array($product->availability_status_id, [
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_OF_STOCK,
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_ORDER,
    ], true);
    $numericPrice = $hasPrice ? (float) $product->price : 0;
    $galleryItems = collect();

    if ($product->main_image_url) {
        $galleryItems->push([
            'url' => $product->main_image_url,
            'color_id' => 0,
            'is_interior' => false,
        ]);
    }

    foreach ($productGallery as $galleryImage) {
        if (filled($galleryImage->gallery_image_url)) {
            $galleryItems->push([
                'url' => $galleryImage->gallery_image_url,
                'color_id' => (int) ($galleryImage->color_id ?? 0),
                'is_interior' => (int) ($galleryImage->color_id ?? 0) === 0,
            ]);
        }
    }

    $galleryItems = $galleryItems->unique('url')->values();
    if ($galleryItems->isEmpty()) {
        $galleryItems->push([
            'url' => asset('assets/images/no-image.png'),
            'color_id' => 0,
            'is_interior' => false,
        ]);
    }

    $displayFields = $product->productType->fields
        ->where('as_image', '!=', true)
        ->where('display_on_single', true)
        ->map(function ($customField) use ($product) {
            $rawFieldValue = $product->getCustomFieldValue($customField->id);
            $displayFieldValue = null;

            if ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING) {
                $displayFieldValue = is_array($rawFieldValue) ? implode(', ', $rawFieldValue) : $rawFieldValue;
            } elseif ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                $ids = is_array($rawFieldValue) ? $rawFieldValue : [$rawFieldValue];
                $displayFieldValue = $customField->options
                    ->whereIn('id', array_filter($ids))
                    ->pluck('name')
                    ->filter()
                    ->implode(', ');
            }

            return [
                'name' => $customField->field_name,
                'value' => trim((string) $displayFieldValue),
            ];
        })
        ->filter(fn ($field) => filled($field['value']))
        ->values();

    $toplineParts = collect([$product->brand?->name, $displayFields->first()['value'] ?? null])->filter()->unique();
    $firstColor = $product->colors->first();
    $reviewCount = (int) data_get($productRatingSummary, 'count', 0);
    $reviewAverage = data_get($productRatingSummary, 'average');
    $descriptionAvailable = filled(strip_tags($productDescriptionHtml));
    $specificationsAvailable = $characteristics->isNotEmpty();
    $firstTab = $descriptionAvailable ? 'description' : ($specificationsAvailable ? 'specs' : 'reviews');
    $deliveryUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info');
    $catalogLink = $catalogUrl;
@endphp

<div class="bona-product-page product-page product-v1" data-product-reference>
    <div class="container">
        <nav class="breadcrumbs product-breadcrumbs" aria-label="{{ trans('base.breadcrumbs') }}">
            <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.home') }}">{{ trans('base.home') }}</a>
            <span aria-hidden="true">/</span>
            <a href="{{ $catalogUrl }}">{{ $product->productType->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ $product->name }}</span>
        </nav>

        <section class="product-hero" aria-labelledby="product-title">
            <div class="product-gallery" data-product-gallery aria-label="{{ $isRussian ? 'Галерея товара' : 'Галерея товару' }}">
                <div class="product-gallery__main{{ $galleryItems->first()['is_interior'] ? ' is-interior' : '' }}" data-gallery-main>
                    <img
                        id="product-main-image"
                        data-gallery-image
                        src="{{ $galleryItems->first()['url'] }}"
                        alt="{{ $product->name }}"
                        width="760"
                        height="900"
                        fetchpriority="high"
                        decoding="async"
                    >

                    @if($availability && $product->availability_status_id !== \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_NONE)
                        <div class="product-gallery__badges"><span class="is-stock">{{ $availability['name'] }}</span></div>
                    @endif

                    @if($galleryItems->count() > 1)
                        <button class="gallery-arrow gallery-arrow--prev" type="button" data-gallery-prev aria-label="{{ $isRussian ? 'Предыдущее фото' : 'Попереднє фото' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m14.5 6-6 6 6 6"></path></svg>
                        </button>
                        <button class="gallery-arrow gallery-arrow--next" type="button" data-gallery-next aria-label="{{ $isRussian ? 'Следующее фото' : 'Наступне фото' }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m9.5 6 6 6-6 6"></path></svg>
                        </button>
                        <span class="product-gallery__counter"><b data-gallery-current>01</b> / <span data-gallery-total>{{ str_pad((string) $galleryItems->count(), 2, '0', STR_PAD_LEFT) }}</span></span>
                    @endif
                </div>

                @if($galleryItems->count() > 1)
                    <div class="product-gallery__thumbs" role="list" aria-label="{{ $isRussian ? 'Миниатюры изображений' : 'Мініатюри зображень' }}">
                        @foreach($galleryItems as $galleryItem)
                            <button
                                class="product-gallery__thumb{{ $loop->first ? ' is-active' : '' }}"
                                type="button"
                                data-gallery-thumb
                                data-image="{{ $galleryItem['url'] }}"
                                data-color-id="{{ $galleryItem['color_id'] }}"
                                data-interior="{{ $galleryItem['is_interior'] ? 'true' : 'false' }}"
                                data-alt="{{ $product->name }}"
                                aria-label="{{ ($isRussian ? 'Фото ' : 'Фото ').$loop->iteration }}"
                                aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                            >
                                <img src="{{ $galleryItem['url'] }}" alt="" width="180" height="120" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="product-buybox">
                <div class="product-buybox__topline">
                    <span>{{ $toplineParts->join(' · ') ?: $product->productType->name }}</span>
                    @if($availability && $product->availability_status_id !== \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_NONE)
                        <span class="product-stock"><i aria-hidden="true"></i>{{ $availability['name'] }}</span>
                    @endif
                </div>

                <h1 id="product-title">{{ $product->name }}</h1>

                @if($reviewAverage || $product->sku)
                    <div class="product-meta">
                        @if($reviewAverage)
                            <a class="product-rating" href="#product-details" data-open-reviews aria-label="{{ trans('base.review_rating', ['rating' => $reviewAverage]) }}">
                                <span aria-hidden="true">★★★★★</span><b>{{ $reviewAverage }}</b><u>{{ trans('base.product_review_based_on', ['COUNT' => $reviewCount]) }}</u>
                            </a>
                        @else
                            <span></span>
                        @endif
                        @if($product->sku)<span>{{ trans('base.sku') }}: {{ $product->sku }}</span>@endif
                    </div>
                @endif

                @if(filled(strip_tags($productShortHtml)))
                    <div class="product-intro bona-content-richtext">{!! $productShortHtml !!}</div>
                @endif

                @if($product->colors->isNotEmpty())
                    <div class="product-option product-option--colors">
                        <div class="product-option__head">
                            <span>{{ trans('base.color') }}</span>
                            <strong id="selected-color">{{ $firstColor?->name }}</strong>
                        </div>
                        <div class="color-options art-colors-list" role="group" aria-label="{{ trans('base.color') }}">
                            @foreach($product->colors as $colorItem)
                                <span
                                    class="color-option color-btn{{ $loop->first ? ' is-active color-selected' : '' }}{{ $colorItem->hex === '#fff' ? ' art-white' : '' }}{{ $colorItem->display_as_image ? ' has-image' : '' }}"
                                    tabindex="0"
                                    role="button"
                                    aria-label="{{ trans('base.catalog_select_color', ['color' => $colorItem->name]) }}"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                    data-color-id="{{ $colorItem->id }}"
                                    data-color-label="{{ $colorItem->name }}"
                                    data-name="{{ json_encode(['id' => $colorItem->id, 'name' => $colorItem->getRawOriginal('name')]) }}"
                                    data-price="{{ $colorItem->pivot->price ?? 0 }}"
                                    @if($colorItem->display_as_image) style="--swatch-image: url('{{ $colorItem->image_url }}');" @else style="--swatch: {{ $colorItem->hex ?: '#e7e2d8' }};" @endif
                                >
                                    @if($colorItem->display_as_image)
                                        <img src="{{ $colorItem->image_url }}" alt="" loading="lazy" decoding="async">
                                    @endif
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(collect($attributeOptions)->flatten(2)->isNotEmpty())
                    <div class="product-options-row">
                        @foreach($attributeOptions as $id => $allOptions)
                            @foreach($allOptions as $name => $options)
                                @if(count($options))
                                    <label class="select-option">
                                        <span>{{ $name }}</span>
                                        <select name="option" id="option-id-{{ $id }}" class="art-select-attribute">
                                            <option value="">— {{ trans('base.select') }} —</option>
                                            @foreach($options as $item)
                                                <option value="{{ json_encode(['id' => $item['id'], 'name' => $item->getRawOriginal('name')]) }}" data-price="{{ $item['price'] }}">
                                                    {{ $item['name'] }}@if((float) $item['price'] > 0) · {{ $item['price'] }} {{ $baseCurrency->name_short }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                @endif
                            @endforeach
                        @endforeach
                    </div>
                @endif

                @if(count($categoryProducts))
                    <div class="product-option product-option--kit">
                        <div class="product-option__head">
                            <span>{{ $isRussian ? 'Комплектация' : 'Комплектація' }}</span>
                            <strong id="kit-count" data-selected-label="{{ $isRussian ? 'выбрано' : 'обрано' }}">{{ $isRussian ? 'Только полотно' : 'Лише полотно' }}</strong>
                        </div>
                        <button class="kit-config-trigger" id="kit-open" type="button" data-fancybox data-src="#product-kit-dialog">
                            <span class="kit-config-trigger__icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4.5 21V3h15v18"></path><path d="M7.5 21V6h9v15"></path><path d="M13.5 13h.01"></path><path d="M3 21h18"></path></svg></span>
                            <span><b>{{ $isRussian ? 'Выбрать комплектацию' : 'Обрати комплектацію' }}</b><small id="kit-summary">{{ $isRussian ? 'Короб, петли, механизм и другие элементы' : 'Короб, завіси, механізм та інші елементи' }}</small></span>
                            <span class="kit-config-trigger__arrow" aria-hidden="true">→</span>
                        </button>

                        <div class="product-kit-selections" aria-live="polite">
                            @foreach($categoryProducts as $categoryName => $subProducts)
                                @php($subDialogId = 'product-kit-group-'.\Illuminate\Support\Str::slug($categoryName).'-'.$loop->index)
                                <div class="added-sub-products" data-wrapper="{{ $subDialogId }}"></div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($hasPrice)
                    <div class="product-price-row">
                        <div>
                            <span>{{ $isRussian ? 'Стоимость комплекта' : 'Вартість комплекту' }}</span>
                            <strong><span id="product-price" data-count="1" data-start-price="{{ $numericPrice }}" data-product-price="{{ $numericPrice }}">{{ number_format($numericPrice, 0, '.', ' ') }}</span> <span>{{ $baseCurrency->name_short }}</span></strong>
                            @if((float) $product->old_price > $numericPrice)<del>{{ number_format((float) $product->old_price, 0, '.', ' ') }} {{ $baseCurrency->name_short }}</del>@endif
                        </div>
                        <small>{{ $isRussian ? 'Точная сумма после замера' : 'Точна сума після заміру' }}</small>
                    </div>

                    <div class="installment-card" id="purchase-installments" data-installment-card>
                        <div class="installment-card__head"><span>{{ $isRussian ? 'Покупка частями' : 'Покупка частинами' }}</span><a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info') }}">{{ $isRussian ? 'Условия' : 'Умови' }}</a></div>
                        <div class="installment-card__providers" role="tablist" aria-label="{{ $isRussian ? 'Банк для оплаты частями' : 'Банк для оплати частинами' }}">
                            <button class="provider-button is-active" type="button" data-provider="mono" role="tab" aria-selected="true"><img src="{{ Vite::asset('bona-html/monobank-logo.svg') }}" alt=""><span>mono</span></button>
                            <button class="provider-button" type="button" data-provider="privat" role="tab" aria-selected="false"><img src="{{ Vite::asset('bona-html/privatbank-chastyny.svg') }}" alt=""><span>ПриватБанк</span></button>
                        </div>
                        <div class="installment-card__calc">
                            <div><small>{{ $isRussian ? 'Ежемесячный платеж' : 'Щомісячний платіж' }}</small><strong><span data-monthly-payment>{{ number_format((int) ceil($numericPrice / 3), 0, '.', ' ') }}</span> {{ $baseCurrency->name_short }}</strong></div>
                            <div class="month-stepper"><button type="button" data-months-minus aria-label="{{ $isRussian ? 'Уменьшить количество платежей' : 'Зменшити кількість платежів' }}">−</button><span><b data-months-value>3</b> {{ $isRussian ? 'платежа' : 'платежі' }}</span><button type="button" data-months-plus aria-label="{{ $isRussian ? 'Увеличить количество платежей' : 'Збільшити кількість платежів' }}">+</button></div>
                            <button class="installment-buy btn-one-click" type="button" data-fancybox data-src="#dialog-buy-one-click"><span>{{ $isRussian ? 'Купить в кредит' : 'Купити в кредит' }}</span><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 10h12M12 6l4 4-4 4"></path></svg></button>
                        </div>
                    </div>
                @else
                    <span id="product-price" data-count="1" data-start-price="0" data-product-price="0" hidden>0</span>
                @endif

                <div id="count-of-products-body" class="product-reference-quantity" aria-hidden="true">
                    <button type="button" class="counter minus" tabindex="-1"></button>
                    <input type="number" id="count-of-products" min="1" value="1" tabindex="-1">
                    <button type="button" class="counter plus" tabindex="-1"></button>
                </div>

                <div class="product-cta">
                    @if($canPurchase)
                        <button class="product-add single-product-add-to-cart" data-product-slug="{{ $product->slug }}" type="button">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><rect x="5" y="8" width="14" height="12" rx="2"></rect><path d="M8.5 8V6.5a3.5 3.5 0 0 1 7 0V8"></path></svg><span>{{ trans('base.add_to_cart') }}</span>
                        </button>
                        <button class="product-oneclick btn-one-click" type="button" data-fancybox data-src="#dialog-buy-one-click">{{ trans('base.buy_in_one_click') }}</button>
                    @else
                        <button class="product-add" type="button" data-fancybox data-src="#order-request"><span>{{ trans('base.leave_request') }}</span></button>
                    @endif
                </div>
                <p class="product-note"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 10v6m0-9v.2"></path></svg>{{ $isRussian ? 'Финальная стоимость зависит от проёма, наличников и фурнитуры. Бесплатно уточним комплектацию до оплаты.' : 'Фінальна вартість залежить від прорізу, лиштви та фурнітури. Безкоштовно уточнимо комплектацію до оплати.' }}</p>
            </div>

            <aside class="product-services" aria-label="{{ $isRussian ? 'Доставка и помощь' : 'Доставка та допомога' }}">
                <div class="product-services__actions" aria-label="{{ $isRussian ? 'Действия с товаром' : 'Дії з товаром' }}">
                    <button
                        class="link-heart product-wish-list-button single-product-wish-list{{ $isWishlisted ? ' link-heart-active is-active' : '' }}"
                        id="{{ $product->slug }}"
                        type="button"
                        aria-label="{{ trans('base.add_to_wish_list') }}"
                        aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"
                    ><x-wish-heart /><span>{{ trans('base.add_to_wish_list') }}</span></button>
                    <button
                        type="button"
                        aria-label="{{ trans('base.add_to_compare') }}"
                        aria-pressed="false"
                        data-product-compare
                        data-product-slug="{{ $product->slug }}"
                        data-add-label="{{ trans('base.add_to_compare') }}"
                        data-remove-label="{{ trans('base.remove_from_compare') }}"
                    ><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 4v16M19 4v16M2.5 7H8M16 17h5.5"></path><path d="m5.8 4.5 2.3 2.4-2.3 2.4M18.2 14.5l-2.3 2.4 2.3 2.4"></path></svg><span>{{ trans('base.add_to_compare') }}</span></button>
                </div>

                <section class="service-card service-card--delivery" id="delivery">
                    <div class="service-card__eyebrow">{{ trans('base.delivery') }}</div>
                    <h2>{{ $isRussian ? 'Получите удобно' : 'Отримайте зручно' }}</h2>
                    <ul class="delivery-list">
                        <li><span class="delivery-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7z"></path><circle cx="7" cy="18" r="2"></circle><circle cx="18" cy="18" r="2"></circle></svg></span><span><b>{{ $isRussian ? 'Новая Почта' : 'Нова Пошта' }}</b><small>{{ $isRussian ? 'В отделение или по адресу' : 'До відділення або адреси' }}</small></span></li>
                        <li><span class="delivery-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 6h16v12H4zM4 9l8 5 8-5"></path></svg></span><span><b>Meest</b><small>{{ $isRussian ? 'В ваше отделение' : 'У ваше відділення' }}</small></span></li>
                        <li><span class="delivery-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M3 7h11v10H3zM14 10h4l3 3v4h-7zM7 12h4"></path></svg></span><span><b>SAT</b><small>{{ $isRussian ? 'Крупногабаритная доставка' : 'Великогабаритна доставка' }}</small></span></li>
                        <li><span class="delivery-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 21s7-5.2 7-12a7 7 0 1 0-14 0c0 6.8 7 12 7 12Z"></path><circle cx="12" cy="9" r="2.3"></circle></svg></span><span><b>{{ $isRussian ? 'Одесса' : 'Одеса' }}</b><small>{{ $isRussian ? 'Адресно или самовывоз' : 'Адресно або самовивіз' }}</small></span></li>
                    </ul>
                    <a class="service-card__link" href="{{ $deliveryUrl }}">{{ $isRussian ? 'Подробные условия' : 'Детальні умови' }} <span aria-hidden="true">→</span></a>
                </section>

                <section class="service-card service-card--config">
                    <div class="config-preview" aria-hidden="true">
                        <img src="{{ Vite::asset('bona-html/img/interior-bedroom.jpg') }}" alt="" width="420" height="240" loading="lazy" decoding="async">
                        <img src="{{ Vite::asset('bona-html/img/interior-apartment.jpg') }}" alt="" width="420" height="240" loading="lazy" decoding="async">
                        <span class="config-preview__label">{{ $isRussian ? 'Стиль · цвет · бюджет' : 'Стиль · колір · бюджет' }}</span>
                    </div>
                    <div class="service-card__eyebrow">{{ $isRussian ? 'Конфигуратор дверей' : 'Конфігуратор дверей' }}</div>
                    <h2>{{ $isRussian ? 'Увидьте двери в своём интерьере' : 'Побачте двері у своєму інтер’єрі' }}</h2>
                    <p>{{ $isRussian ? 'Выберите стиль, цвет и бюджет — покажем модели, которые подойдут именно вашему пространству.' : 'Оберіть стиль, колір і бюджет — покажемо моделі, які пасують саме вашому простору.' }}</p>
                    <button class="service-card__button" type="button" data-fancybox data-src="#dialog-call-consultation">{{ $isRussian ? 'Подобрать свои двери' : 'Підібрати свої двері' }} <span aria-hidden="true">→</span></button>
                </section>
            </aside>
        </section>

        <section class="product-details-grid" aria-label="{{ $isRussian ? 'Детали товара и консультация' : 'Деталі товару та консультація' }}">
            <section class="product-info-tabs" id="product-details" aria-label="{{ $isRussian ? 'Информация о товаре' : 'Інформація про товар' }}">
                <div class="product-info-tabs__list" role="tablist" aria-label="{{ $isRussian ? 'Детали товара' : 'Деталі товару' }}">
                    @if($descriptionAvailable)
                        <button class="{{ $firstTab === 'description' ? 'is-active' : '' }}" id="tab-description" type="button" role="tab" aria-selected="{{ $firstTab === 'description' ? 'true' : 'false' }}" aria-controls="panel-description" data-product-tab="description">{{ trans('base.description') }}</button>
                    @endif
                    @if($specificationsAvailable)
                        <button class="{{ $firstTab === 'specs' ? 'is-active' : '' }}" id="tab-specs" type="button" role="tab" aria-selected="{{ $firstTab === 'specs' ? 'true' : 'false' }}" aria-controls="panel-specs" data-product-tab="specs">{{ trans('base.characteristics') }}</button>
                    @endif
                    <button class="{{ $firstTab === 'reviews' ? 'is-active' : '' }}" id="tab-reviews" type="button" role="tab" aria-selected="{{ $firstTab === 'reviews' ? 'true' : 'false' }}" aria-controls="panel-reviews" data-product-tab="reviews">{{ trans('base.product_reviews_title') }} @if($reviewCount)<span>{{ $reviewCount }}</span>@endif</button>
                </div>

                @if($descriptionAvailable)
                    <div class="product-info-tabs__panel{{ $firstTab === 'description' ? ' is-active' : '' }}" id="panel-description" role="tabpanel" aria-labelledby="tab-description" data-product-panel="description" @if($firstTab !== 'description') hidden @endif>
                        <div class="tab-description tab-description--plain bona-content-richtext">{!! $productDescriptionHtml !!}</div>
                    </div>
                @endif

                @if($specificationsAvailable)
                    <div class="product-info-tabs__panel{{ $firstTab === 'specs' ? ' is-active' : '' }}" id="panel-specs" role="tabpanel" aria-labelledby="tab-specs" data-product-panel="specs" @if($firstTab !== 'specs') hidden @endif>
                        <div class="tab-specs">
                            @foreach($characteristics as $characteristic)
                                @if(filled($characteristic['name']) || filled($characteristic['value']))
                                    <div><span>{{ $characteristic['name'] }}</span><strong>{{ $characteristic['value'] }}</strong></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="product-info-tabs__panel{{ $firstTab === 'reviews' ? ' is-active' : '' }}" id="panel-reviews" role="tabpanel" aria-labelledby="tab-reviews" data-product-panel="reviews" @if($firstTab !== 'reviews') hidden @endif>
                    <div class="tab-reviews" id="reviews">
                        <div class="tab-reviews__score">
                            <strong>{{ $reviewAverage ?: '—' }}</strong>
                            <span aria-hidden="true">{{ $reviewAverage ? '★★★★★' : '☆☆☆☆☆' }}</span>
                            <small>{{ $reviewCount ? trans('base.product_review_based_on', ['COUNT' => $reviewCount]) : trans('base.product_reviews_empty') }}</small>
                            <button class="reviews-write art-product-reviews__open" type="button" data-fancybox data-src="#dialog-product-review">{{ trans('base.product_review_leave') }} <span aria-hidden="true">→</span></button>
                        </div>
                        <div class="review-list">
                            @forelse($productReviews as $review)
                                @php($initials = \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($review->author_name, 0, 2)))
                                <article>
                                    <div><span class="review-avatar">{{ $initials }}</span><p><b>{{ $review->author_name }}</b><small>{{ optional($review->publishedDate())->translatedFormat('d.m.Y') }}</small></p><span class="review-stars" aria-label="{{ trans('base.review_rating', ['rating' => $review->rating]) }}">{{ str_repeat('★', (int) $review->rating) }}{{ str_repeat('☆', 5 - (int) $review->rating) }}</span></div>
                                    <p>{{ $review->review }}</p>
                                </article>
                            @empty
                                <p class="product-reviews-empty">{{ trans('base.product_reviews_empty') }}</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </section>

            <section class="service-card service-card--manager product-details-consultation" id="consultation">
                <div class="manager-mini"><img src="{{ Vite::asset('bona-html/img/manager-oksana.webp') }}" alt="{{ $isRussian ? 'Оксана, консультант Bona Doors' : 'Оксана, консультант Bona Doors' }}" width="94" height="94" loading="lazy" decoding="async"><span><b>Оксана</b><small>{{ $isRussian ? 'Консультант Bona Doors' : 'Консультант Bona Doors' }}</small></span><i aria-hidden="true"></i></div>
                <h2>{{ $isRussian ? 'Нужна помощь с комплектацией?' : 'Потрібна допомога з комплектацією?' }}</h2>
                <p>{{ $isRussian ? 'Бесплатно проверим размеры, совместимость короба и фурнитуры и подготовим точную смету.' : 'Безкоштовно перевіримо розміри, сумісність короба й фурнітури та підготуємо точний кошторис.' }}</p>
                <button class="service-card__link" type="button" data-fancybox data-src="#dialog-call-consultation">{{ $isRussian ? 'Посоветоваться с Оксаной' : 'Порадитися з Оксаною' }} <span aria-hidden="true">→</span></button>
            </section>
        </section>
    </div>

    @foreach($preparedContentBlocks as $block)
        @if(($block['type'] ?? '') === 'text')
            <section class="product-story product-reference-editorial bona-product-editorial bona-product-editorial--text" id="overview-{{ $loop->iteration }}"><div class="container">
                <div class="product-section-head">
                    <div>@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif</div>
                    @if(filled(strip_tags($block['_content'])))<div class="product-reference-copy bona-content-richtext">{!! $block['_content'] !!}</div>@endif
                </div>
            </div></section>
        @elseif(($block['type'] ?? '') === 'image_text')
            <section class="product-story product-reference-editorial"><div class="container">
                <div class="product-section-head">
                    <div>@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif</div>
                    @if(filled(strip_tags($block['_content'])))<div class="product-reference-copy bona-content-richtext">{!! $block['_content'] !!}</div>@endif
                </div>
                @if($block['_image_url'])<figure class="product-reference-editorial__image{{ ($block['image_position'] ?? 'left') === 'right' ? ' is-right' : '' }}"><img src="{{ $block['_image_url'] }}" alt="{{ $block['_title'] ?: $product->name }}" width="1328" height="720" loading="lazy" decoding="async"></figure>@endif
                @if($block['_button_label'] && filled($block['button_url'] ?? null))<a class="product-reference-editorial__button" href="{{ $block['button_url'] }}">{{ $block['_button_label'] }} <span aria-hidden="true">→</span></a>@endif
            </div></section>
        @elseif(($block['type'] ?? '') === 'features' && $block['_items']->isNotEmpty())
            <section class="product-benefits product-reference-editorial"><div class="container">
                <div class="product-section-head product-section-head--compact"><div>@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif</div></div>
                <div class="benefits-grid">
                    @foreach($block['_items'] as $item)
                        <article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><svg viewBox="0 0 36 36" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="18" cy="18" r="12"></circle><path d="m12 18 4 4 8-9"></path></svg>@if($item['title'])<h3>{{ $item['title'] }}</h3>@endif @if($item['text'])<p>{{ $item['text'] }}</p>@endif</article>
                    @endforeach
                </div>
            </div></section>
        @elseif(($block['type'] ?? '') === 'quote' && filled(strip_tags($block['_quote'])))
            <section class="product-reference-quote"><div class="container"><blockquote>{!! $block['_quote'] !!}</blockquote>@if($block['_author'])<cite>{{ $block['_author'] }}</cite>@endif</div></section>
        @endif
    @endforeach

    @if($productVideos->isNotEmpty())
        <section class="bona-product-videos product-reference-utility" aria-labelledby="product-videos-title"><div class="container">
            <header class="product-section-head product-section-head--compact"><div><div class="kicker">Video</div><h2 id="product-videos-title">{{ trans('base.open_systems') }}</h2></div></header>
            <div class="bona-product-videos__grid">
                @foreach($productVideos as $video)
                    @if(filled((string) $video->iframe))<article>@if($video->tab)<h3>{{ $video->tab }}</h3>@endif<div class="bona-product-video-frame">{!! $video->iframe !!}</div></article>@endif
                @endforeach
            </div>
        </div></section>
    @endif

    @if($faqItems->isNotEmpty())
        <section class="bona-product-faq product-reference-utility" aria-labelledby="product-faq-title"><div class="container bona-product-faq__grid">
            <header><div class="kicker">FAQ</div><h2 id="product-faq-title">{{ $isRussian ? 'Вопросы о модели' : 'Питання про модель' }}</h2></header>
            <div>@foreach($faqItems as $faq)<details><summary>{{ $faq->question }}<span aria-hidden="true">+</span></summary><div class="bona-content-richtext">{!! $faq->answer !!}</div></details>@endforeach</div>
        </div></section>
    @endif

    @if($seoSectionTitle || filled(strip_tags($seoSectionContent)))
        <section class="bona-product-seo product-reference-utility"><div class="container bona-product-seo__inner">
            @if($seoSectionTitle)<h2>{{ $seoSectionTitle }}</h2>@endif
            @if(filled(strip_tags($seoSectionContent)))<div class="bona-content-richtext">{!! $seoSectionContent !!}</div>@endif
        </div></section>
    @endif

    <section class="consult-strip"><div class="container consult-strip__inner">
        <div class="consult-strip__person"><img src="{{ Vite::asset('bona-html/img/manager-oksana.webp') }}" alt="Оксана, консультант Bona Doors" width="300" height="300" loading="lazy" decoding="async"><span><i aria-hidden="true"></i><small>{{ $isRussian ? 'Оксана сейчас на связи' : 'Оксана зараз на зв’язку' }}</small></span></div>
        <div><div class="kicker">{{ $isRussian ? 'Бесплатная консультация' : 'Безкоштовна консультація' }}</div><h2>{{ $isRussian ? 'Не уверены в размере или комплектации?' : 'Не впевнені в розмірі чи комплектації?' }}</h2><p>{{ $isRussian ? 'Пришлите фото проёма — Оксана подскажет, что именно нужно, и подготовит предварительную смету.' : 'Надішліть фото прорізу — Оксана підкаже, що саме потрібно, і підготує попередній кошторис.' }}</p></div>
        <button type="button" data-fancybox data-src="#dialog-call-consultation">{{ $isRussian ? 'Получить консультацию' : 'Отримати консультацію' }} <span aria-hidden="true">→</span></button>
    </div></section>

    @if(count($sameTypeProducts))
        <section class="related-products" aria-labelledby="related-products-title"><div class="container">
            <div class="sec-head sec-head--split"><div><div class="kicker">{{ $isRussian ? 'Смотрите также' : 'Дивіться також' }}</div><h2 class="sec-title" id="related-products-title">{{ $isRussian ? 'Похожие модели' : 'Схожі моделі' }}</h2></div><a class="sec-link" href="{{ $catalogLink }}">{{ $isRussian ? 'Все модели категории' : 'Усі моделі категорії' }}</a></div>
            <div class="related-grid related-grid--home related-grid--dynamic">
                @foreach(collect($sameTypeProducts)->take(4) as $relatedProduct)
                    @include('pages.store.partials.product_item', ['product' => $relatedProduct, 'baseCurrency' => $baseCurrency])
                @endforeach
            </div>
        </div></section>
    @endif

    @if($canPurchase)
        <div class="mobile-buybar" aria-label="{{ $isRussian ? 'Быстрая покупка' : 'Швидка покупка' }}">
            <div><span>{{ $product->name }}</span><strong><span data-mobile-price>{{ number_format($numericPrice, 0, '.', ' ') }}</span> {{ $baseCurrency->name_short }}</strong></div>
            <button class="single-product-add-to-cart" data-product-slug="{{ $product->slug }}" type="button">{{ $isRussian ? 'В корзину' : 'До кошика' }}</button>
        </div>
    @endif
</div>

@if(count($categoryProducts))
    <div id="product-kit-dialog" class="product-kit-dialog" aria-hidden="true">
        <div class="kicker">{{ $isRussian ? 'Комплектация дверей' : 'Комплектація дверей' }}</div>
        <h2>{{ $isRussian ? 'Соберите свой комплект' : 'Зберіть свій комплект' }}</h2>
        <p>{{ $isRussian ? 'Выберите нужные элементы — полная стоимость обновится автоматически.' : 'Оберіть потрібні елементи — повна вартість оновиться автоматично.' }}</p>
        <div class="product-kit-dialog__groups">
            @foreach($categoryProducts as $categoryName => $subProducts)
                @php($subDialogId = 'product-kit-group-'.\Illuminate\Support\Str::slug($categoryName).'-'.$loop->index)
                <section id="{{ $subDialogId }}" class="art-popup-single-product product-kit-dialog__group">
                    <h3>{{ $categoryName }}</h3>
                    <div class="art-popup-list-sub-products product-kit-dialog__grid">
                        @foreach($subProducts as $subProduct)
                            <article class="art-product-item">
                                <div class="art-product-data">
                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}" class="art-product-link">
                                        <span class="image"><img src="{{ $subProduct->preview_image_url }}" alt="{{ $subProduct->name }}" width="220" height="220" loading="lazy" decoding="async"></span>
                                        <span class="text"><span class="product-title">{{ $subProduct->name }}</span><span class="price-wrapper"><span class="price">{{ $subProduct->price }}</span> <span class="currency">{{ $baseCurrency->name_short }}</span></span></span>
                                    </a>
                                    <button type="button" class="single-sub-product-add-to-cart" data-count="0" data-added="0" data-id="{{ $subProduct->id }}" data-slug="{{ $subProduct->slug }}">{{ trans('base.select') }}</button>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    </div>
@endif

<x-store.call-consultation-modal :options="$applicationGlobalOptions" />
