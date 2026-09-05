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
    $displayReviewCount = $reviewCount ?: 3;
    $displayReviewAverage = $reviewAverage ?: 4.9;
    $descriptionAvailable = filled(strip_tags($productDescriptionHtml));
    $specificationsAvailable = $characteristics->isNotEmpty();
    $firstTab = $descriptionAvailable ? 'description' : ($specificationsAvailable ? 'specs' : 'reviews');
    $deliveryUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.delivery-info');
    $checkoutUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.checkout.page');
    $monoPeriods = \App\Support\Payment\InstallmentPeriods::for('monobank');
    $privatPeriods = \App\Support\Payment\InstallmentPeriods::for('privatbank');
    $initialInstallmentPeriod = $monoPeriods[0] ?? 3;
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

        <nav class="product-section-nav" data-product-section-nav aria-label="{{ $isRussian ? 'Разделы товара' : 'Розділи товару' }}">
            <div class="product-section-nav__list">
                <button class="is-active" type="button" data-product-overview aria-current="true">
                    {{ trans('base.product_about') }}
                </button>
                @if($descriptionAvailable)
                    <button id="tab-description" type="button" data-product-tab="description" aria-controls="panel-description" aria-pressed="false">{{ trans('base.description') }}</button>
                @endif
                @if($specificationsAvailable)
                    <button id="tab-specs" type="button" data-product-tab="specs" aria-controls="panel-specs" aria-pressed="false">{{ trans('base.characteristics') }}</button>
                @endif
                <button id="tab-reviews" type="button" data-product-tab="reviews" aria-controls="panel-reviews" aria-pressed="false">{{ trans('base.product_reviews_title') }} <span>{{ $displayReviewCount }}</span></button>
            </div>
        </nav>

        <section class="product-hero" id="product-overview" aria-labelledby="product-title">
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

                <div class="product-meta">
                    <a class="product-rating" href="#product-details" data-open-reviews aria-label="{{ trans('base.review_rating', ['rating' => $displayReviewAverage]) }}">
                        <span aria-hidden="true">★★★★★</span><b>{{ $displayReviewAverage }}</b><u>{{ trans('base.product_review_based_on', ['COUNT' => $displayReviewCount]) }}</u>
                    </a>
                    @if($product->sku)<span>{{ trans('base.sku') }}: {{ $product->sku }}</span>@endif
                </div>

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
                        <button class="kit-config-trigger" id="kit-open" type="button" data-product-dialog-open="product-kit-dialog">
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
                            <strong><span class="product-price-row__amount" id="product-price" data-count="1" data-start-price="{{ $numericPrice }}" data-product-price="{{ $numericPrice }}">{{ number_format($numericPrice, 0, '.', ' ') }}</span> <span class="product-price-row__currency">{{ $baseCurrency->name_short }}</span></strong>
                            @if((float) $product->old_price > $numericPrice)<del>{{ number_format((float) $product->old_price, 0, '.', ' ') }} {{ $baseCurrency->name_short }}</del>@endif
                        </div>
                        <small>{{ $isRussian ? 'Точная сумма после замера' : 'Точна сума після заміру' }}</small>
                    </div>

                    <div class="installment-card" id="purchase-installments" data-installment-card>
                        <div class="installment-card__head"><span>{{ $isRussian ? 'Покупка частями' : 'Покупка частинами' }}</span><button type="button" data-installment-terms-open>{{ $isRussian ? 'Условия' : 'Умови' }}</button></div>
                        <div class="installment-card__providers" role="tablist" aria-label="{{ $isRussian ? 'Банк для оплаты частями' : 'Банк для оплати частинами' }}">
                            <button class="provider-button is-active" type="button" data-provider="mono" data-payment-type="{{ \App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART_MONO_BANK }}" data-periods='@json($monoPeriods)' role="tab" aria-selected="true"><img src="{{ Vite::asset('bona-html/monobank-logo.svg') }}" alt=""><span>mono</span></button>
                            <button class="provider-button" type="button" data-provider="privat" data-payment-type="{{ \App\DataClasses\PaymentTypesDataClass::CARD_PAYMENT_PAYPART }}" data-periods='@json($privatPeriods)' role="tab" aria-selected="false"><img src="{{ Vite::asset('bona-html/privatbank-chastyny.svg') }}" alt=""><span>ПриватБанк</span></button>
                        </div>
                        <div class="installment-card__calc">
                            <div><small>{{ $isRussian ? 'Ежемесячный платеж' : 'Щомісячний платіж' }}</small><strong><span data-monthly-payment>{{ number_format((int) ceil($numericPrice / $initialInstallmentPeriod), 0, '.', ' ') }}</span> {{ $baseCurrency->name_short }}</strong></div>
                            <div class="month-stepper"><button type="button" data-months-minus aria-label="{{ $isRussian ? 'Уменьшить количество платежей' : 'Зменшити кількість платежів' }}">−</button><span><b data-months-value>{{ $initialInstallmentPeriod }}</b> {{ $isRussian ? 'платежа' : 'платежі' }}</span><button type="button" data-months-plus aria-label="{{ $isRussian ? 'Увеличить количество платежей' : 'Збільшити кількість платежів' }}">+</button></div>
                            <button class="installment-buy single-product-add-to-cart" type="button" data-product-slug="{{ $product->slug }}" data-checkout-base="{{ $checkoutUrl }}"><span>{{ $isRussian ? 'Купить в кредит' : 'Купити в кредит' }}</span><svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M4 10h12M12 6l4 4-4 4"></path></svg></button>
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
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M3.5 5h2l1.5 9.2a2 2 0 0 0 2 1.7h7.7a2 2 0 0 0 1.9-1.4L21 8H6.1"></path><circle cx="9.2" cy="19.2" r="1.2"></circle><circle cx="17.2" cy="19.2" r="1.2"></circle></svg><span>{{ trans('base.purchase') }}</span>
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
                    ><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 7h14"></path><path d="m14 3 4 4-4 4"></path><path d="M20 17H6"></path><path d="m10 13-4 4 4 4"></path></svg><span>{{ trans('base.add_to_compare') }}</span></button>
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
                    <a class="service-card__button" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.door-configurator.page') }}">{{ $isRussian ? 'Подобрать свои двери' : 'Підібрати свої двері' }} <span aria-hidden="true">→</span></a>
                </section>
            </aside>
        </section>

        <section class="product-details-grid" aria-label="{{ $isRussian ? 'Детали товара и консультация' : 'Деталі товару та консультація' }}">
            <section class="product-info-tabs" id="product-details" aria-label="{{ $isRussian ? 'Информация о товаре' : 'Інформація про товар' }}">
                @if($descriptionAvailable)
                    <div class="product-info-tabs__panel{{ $firstTab === 'description' ? ' is-active' : '' }}" id="panel-description" role="region" aria-labelledby="tab-description" data-product-panel="description" @if($firstTab !== 'description') hidden @endif>
                        <div class="tab-description tab-description--plain bona-content-richtext">{!! $productDescriptionHtml !!}</div>
                    </div>
                @endif

                @if($specificationsAvailable)
                    <div class="product-info-tabs__panel{{ $firstTab === 'specs' ? ' is-active' : '' }}" id="panel-specs" role="region" aria-labelledby="tab-specs" data-product-panel="specs" @if($firstTab !== 'specs') hidden @endif>
                        <div class="tab-specs">
                            @foreach($characteristics as $characteristic)
                                @if(filled($characteristic['name']) || filled($characteristic['value']))
                                    <div><span>{{ $characteristic['name'] }}</span><strong>{{ $characteristic['value'] }}</strong></div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="product-info-tabs__panel{{ $firstTab === 'reviews' ? ' is-active' : '' }}" id="panel-reviews" role="region" aria-labelledby="tab-reviews" data-product-panel="reviews" @if($firstTab !== 'reviews') hidden @endif>
                    <div class="tab-reviews" id="reviews">
                        <div class="tab-reviews__score">
                            <strong>{{ $displayReviewAverage }}</strong>
                            <span aria-hidden="true">★★★★★</span>
                            <small>{{ trans('base.product_review_based_on', ['COUNT' => $displayReviewCount]) }}</small>
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
                <button class="service-card__link" type="button" data-lead-modal-open="dialog-call-consultation">{{ $isRussian ? 'Получить консультацию' : 'Отримати консультацію' }} <span aria-hidden="true">→</span></button>
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
        @elseif(($block['type'] ?? '') === 'benefits' && $block['_items']->isNotEmpty())
            <section class="product-benefits product-reference-editorial"><div class="container">
                <div class="product-section-head product-section-head--compact"><div>@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif</div></div>
                <div class="benefits-grid">
                    @foreach($block['_items']->take(4) as $item)
                        <article>
                            <span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            @switch($loop->iteration)
                                @case(1)<svg viewBox="0 0 36 36" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 21h5l4 5V10l-4 5H6v6Z"></path><path d="M21 14c2 2 2 6 0 8m4-12c5 5 5 11 0 16"></path></svg>@break
                                @case(2)<svg viewBox="0 0 36 36" fill="none" stroke="currentColor" aria-hidden="true"><path d="M18 5c7 8 10 12 10 17a10 10 0 0 1-20 0c0-5 3-9 10-17Z"></path><path d="M12 23c1 3 3 4 6 4"></path></svg>@break
                                @case(3)<svg viewBox="0 0 36 36" fill="none" stroke="currentColor" aria-hidden="true"><path d="M7 29V7h22v22H7Z"></path><path d="m11 24 6-6 4 4 4-4"></path></svg>@break
                                @default<svg viewBox="0 0 36 36" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="18" cy="18" r="12"></circle><path d="m12 18 4 4 8-9"></path></svg>
                            @endswitch
                            @if($item['title'])<h3>{{ $item['title'] }}</h3>@endif
                            @if($item['text'])<p>{{ $item['text'] }}</p>@endif
                        </article>
                    @endforeach
                </div>
            </div></section>
        @elseif(($block['type'] ?? '') === 'full_kit' && $block['_items']->isNotEmpty())
            <section class="product-kit product-reference-editorial"><div class="container">
                <div class="product-section-head">
                    <div>@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif</div>
                    @if(filled(strip_tags($block['_content'])))<div class="product-reference-copy bona-content-richtext">{!! $block['_content'] !!}</div>@endif
                </div>
                <div class="kit-diagram">
                    <div class="kit-diagram__visual" aria-hidden="true"><div class="door-scheme"><span class="door-scheme__frame"></span><span class="door-scheme__leaf"></span><span class="door-scheme__line"></span><span class="door-scheme__handle"></span></div>@foreach($block['_items']->take(4) as $item)<span class="kit-point kit-point--{{ $loop->iteration }}">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@endforeach</div>
                    <ol class="kit-diagram__list">@foreach($block['_items']->take(4) as $item)<li><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><div>@if($item['title'])<b>{{ $item['title'] }}</b>@endif @if($item['text'])<p>{{ $item['text'] }}</p>@endif</div></li>@endforeach</ol>
                </div>
            </div></section>
        @elseif(($block['type'] ?? '') === 'journey' && $block['_items']->isNotEmpty())
            <section class="delivery-section product-reference-editorial"><div class="container">
                <div class="product-section-head">
                    <div>@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif</div>
                    @if(filled(strip_tags($block['_content'])))<div class="product-reference-copy bona-content-richtext">{!! $block['_content'] !!}</div>@endif
                </div>
                <div class="delivery-steps">@foreach($block['_items']->take(4) as $item)<article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@if($item['title'])<h3>{{ $item['title'] }}</h3>@endif @if($item['text'])<p>{{ $item['text'] }}</p>@endif @if($item['meta'])<small>{{ $item['meta'] }}</small>@endif</article>@endforeach</div>
            </div></section>
        @elseif(($block['type'] ?? '') === 'installments' && $hasPrice)
            <section class="installments-section product-reference-editorial"><div class="container installments-section__inner">
                <div class="installments-section__copy">@if($block['_eyebrow'])<div class="kicker">{{ $block['_eyebrow'] }}</div>@endif @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif @if(filled(strip_tags($block['_content'])))<div class="product-reference-copy bona-content-richtext">{!! $block['_content'] !!}</div>@endif</div>
                <div class="installments-section__cards">
                    <a class="installments-section__card" href="#purchase-installments" data-focus-provider="mono"><div><img src="{{ Vite::asset('bona-html/monobank-logo.svg') }}" alt="monobank"><strong>{{ $isRussian ? 'Покупка частями' : 'Покупка частинами' }}</strong></div><p><b data-installment-example data-provider-example="mono">{{ $isRussian ? 'от' : 'від' }} {{ number_format((int) ceil($numericPrice / max($monoPeriods ?: [1])), 0, '.', ' ') }} {{ $baseCurrency->name_short }}/{{ $isRussian ? 'мес.' : 'міс.' }}</b><span>{{ $isRussian ? 'до' : 'до' }} {{ max($monoPeriods ?: [1]) }} {{ $isRussian ? 'платежей' : 'платежів' }}</span></p><em>{{ $isRussian ? 'Рассчитать платёж' : 'Розрахувати платіж' }} <span>↑</span></em></a>
                    <a class="installments-section__card" href="#purchase-installments" data-focus-provider="privat"><div><img src="{{ Vite::asset('bona-html/privatbank-chastyny.svg') }}" alt="ПриватБанк"><strong>{{ $isRussian ? 'Оплата частями' : 'Оплата частинами' }}</strong></div><p><b data-installment-example data-provider-example="privat">{{ $isRussian ? 'от' : 'від' }} {{ number_format((int) ceil($numericPrice / max($privatPeriods ?: [1])), 0, '.', ' ') }} {{ $baseCurrency->name_short }}/{{ $isRussian ? 'мес.' : 'міс.' }}</b><span>{{ $isRussian ? 'до' : 'до' }} {{ max($privatPeriods ?: [1]) }} {{ $isRussian ? 'платежей' : 'платежів' }}</span></p><em>{{ $isRussian ? 'Рассчитать платёж' : 'Розрахувати платіж' }} <span>↑</span></em></a>
                    <small>{{ $isRussian ? 'Пример рассчитан для текущей комплектации. Условия и лимит определяет банк.' : 'Приклад розраховано для поточної комплектації. Умови та ліміт визначає банк.' }}</small>
                </div>
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
        <button type="button" data-lead-modal-open="dialog-call-consultation">{{ $isRussian ? 'Получить консультацию' : 'Отримати консультацію' }} <span aria-hidden="true">→</span></button>
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

</div>

@if(count($categoryProducts))
    <dialog class="product-dialog kit-dialog" id="product-kit-dialog" data-product-dialog>
        <button class="product-dialog__close" type="button" data-product-dialog-close aria-label="{{ $isRussian ? 'Закрыть' : 'Закрити' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg></button>
        <div class="kit-dialog__top">
            <div class="kit-dialog__intro">
                <div class="kicker">{{ $isRussian ? 'Комплектация дверей' : 'Комплектація дверей' }}</div>
                <h2>{{ $isRussian ? 'Соберите свой комплект' : 'Зберіть свій комплект' }}</h2>
                <p>{{ $isRussian ? 'Выберите по одному совместимому элементу в каждой категории. Итоговая стоимость обновится автоматически.' : 'Оберіть по одному сумісному елементу в кожній категорії. Підсумкова вартість оновиться автоматично.' }}</p>
            </div>
        </div>
        <div class="kit-dialog__layout">
            <div class="kit-dialog__workspace">
                <nav class="kit-builder" data-kit-categories aria-label="{{ $isRussian ? 'Категории комплектации' : 'Категорії комплектації' }}">
                    @foreach($categoryProducts as $categoryName => $subProducts)
                        @php($kitCategoryKey = (\Illuminate\Support\Str::slug((string) $categoryName) ?: 'group').'-'.$loop->index)
                        <button class="kit-builder__item{{ $loop->first ? ' is-active' : '' }}" type="button" data-kit-category="{{ $kitCategoryKey }}" aria-current="{{ $loop->first ? 'step' : 'false' }}">
                            <span class="kit-builder__number">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="kit-builder__copy"><b>{{ $categoryName }}</b><small data-kit-category-summary>{{ $isRussian ? 'Выберите подходящий элемент' : 'Оберіть потрібний елемент' }}</small></span>
                            <span class="kit-builder__state" aria-hidden="true">→</span>
                        </button>
                    @endforeach
                </nav>
                <section class="kit-choice-panel" aria-live="polite">
                    <div class="kit-choice-panel__head">
                        <div><span data-kit-choice-step>{{ $isRussian ? 'Шаг 01' : 'Крок 01' }}</span><h3 data-kit-choice-title>{{ array_key_first($categoryProducts) }}</h3><p>{{ $isRussian ? 'Выберите один совместимый вариант для этой категории.' : 'Оберіть один сумісний варіант для цієї категорії.' }}</p></div>
                    </div>
                    <div class="kit-choice-grid" data-kit-choice-grid>
                        @foreach($categoryProducts as $categoryName => $subProducts)
                            @php($kitCategoryKey = (\Illuminate\Support\Str::slug((string) $categoryName) ?: 'group').'-'.$loop->index)
                            @foreach($subProducts as $subProduct)
                                @php($carrierId = 'kit-carrier-'.$kitCategoryKey.'-'.$subProduct->id)
                                <button class="kit-choice-card" type="button" data-kit-option="{{ $subProduct->id }}" data-kit-option-key="{{ $carrierId }}" data-kit-category-key="{{ $kitCategoryKey }}" data-kit-category-name="{{ $categoryName }}" data-kit-label="{{ $subProduct->name }}" data-kit-price="{{ (float) $subProduct->price }}" data-kit-carrier="{{ $carrierId }}" aria-pressed="false" @if(!$loop->parent->first) hidden @endif>
                                    <span class="kit-choice-card__image"><img src="{{ $subProduct->preview_image_url ?: asset('assets/images/no-image.png') }}" alt="{{ $subProduct->name }}" width="300" height="225" loading="lazy" decoding="async"><i aria-hidden="true">✓</i></span>
                                    <span class="kit-choice-card__copy"><b>{{ $subProduct->name }}</b><small>{{ collect([$subProduct->sku, $subProduct->brand?->name])->filter()->join(' · ') ?: $categoryName }}</small><strong>+{{ number_format((float) $subProduct->price, 0, '.', ' ') }} {{ $baseCurrency->name_short }}</strong><em>{{ $isRussian ? 'Выбрать' : 'Обрати' }}</em></span>
                                </button>
                            @endforeach
                        @endforeach
                    </div>
                </section>
            </div>
        </div>
        <aside class="kit-dialog__summary" aria-label="{{ $isRussian ? 'Итог комплектации' : 'Підсумок комплектації' }}">
            <div class="kit-dialog__selection">
                <div class="kit-dialog__selection-heading">
                    <span class="kit-dialog__selection-label">{{ $isRussian ? 'Выбрано' : 'Обрано' }}</span>
                    <span class="kit-dialog__selection-hint" data-kit-selection-hint hidden>{{ $isRussian ? 'Прокрутите список' : 'Прокрутіть список' }} <span aria-hidden="true">↓</span></span>
                </div>
                <div class="kit-dialog__selection-base"><strong>{{ $isRussian ? 'Дверное полотно' : 'Дверне полотно' }}</strong><b>{{ number_format($numericPrice, 0, '.', ' ') }} {{ $baseCurrency->name_short }}</b></div>
                <ul data-kit-dialog-selected><li>{{ $isRussian ? 'Дополнительные элементы не выбраны' : 'Додаткові елементи не обрані' }}</li></ul>
            </div>
            <div class="kit-dialog__total"><span>{{ $isRussian ? 'Итого' : 'Разом' }}</span><strong data-kit-dialog-total>{{ number_format($numericPrice, 0, '.', ' ') }} {{ $baseCurrency->name_short }}</strong></div>
            <button type="button" data-kit-save>{{ $isRussian ? 'Сохранить комплектацию' : 'Зберегти комплектацію' }}</button>
            <small>{{ $isRussian ? 'Совместимость и размеры проверит менеджер перед оплатой.' : 'Сумісність і розміри перевірить менеджер перед оплатою.' }}</small>
        </aside>
    </dialog>

    <div class="product-kit-cart-data" hidden aria-hidden="true">
        @foreach($categoryProducts as $categoryName => $subProducts)
            @php($kitCategoryKey = (\Illuminate\Support\Str::slug((string) $categoryName) ?: 'group').'-'.$loop->index)
            <div class="art-popup-single-product">
                @foreach($subProducts as $subProduct)
                    @php($carrierId = 'kit-carrier-'.$kitCategoryKey.'-'.$subProduct->id)
                    <article class="art-product-item"><div class="art-product-data"><a class="art-product-link" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}"><span class="text"><span class="product-title">{{ $subProduct->name }}</span><span class="price">{{ (float) $subProduct->price }}</span></span></a><button id="{{ $carrierId }}" type="button" class="single-sub-product-add-to-cart" data-count="0" data-added="0" data-id="{{ $subProduct->id }}" data-slug="{{ $subProduct->slug }}" tabindex="-1"></button></div></article>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

@if($hasPrice)
    <dialog class="product-dialog installment-terms-dialog" id="installment-terms-dialog" data-product-dialog>
        <button class="product-dialog__close" type="button" data-product-dialog-close aria-label="{{ $isRussian ? 'Закрыть' : 'Закрити' }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"></path></svg></button>
        <div class="kicker">{{ $isRussian ? 'Покупка частями' : 'Покупка частинами' }}</div>
        <h2>{{ $isRussian ? 'Условия выбранной программы' : 'Умови обраної програми' }}</h2>
        <section data-terms-provider="mono">
            <div class="installment-terms-dialog__brand"><img src="{{ Vite::asset('bona-html/monobank-logo.svg') }}" alt="monobank"><strong>monobank · {{ $isRussian ? 'Покупка частями' : 'Покупка частинами' }}</strong></div>
            <ol><li>{{ $isRussian ? 'Добавьте комплект в корзину и выберите monobank при оформлении.' : 'Додайте комплект у кошик і оберіть monobank під час оформлення.' }}</li><li>{{ $isRussian ? 'Подтвердите покупку в приложении mono в пределах доступного лимита.' : 'Підтвердьте покупку в застосунку mono в межах доступного ліміту.' }}</li><li>{{ $isRussian ? 'Банк покажет точное количество и график равных платежей до подтверждения.' : 'Банк покаже точну кількість і графік рівних платежів до підтвердження.' }}</li></ol>
            <a href="https://monobank.ua/chast" target="_blank" rel="noopener noreferrer">{{ $isRussian ? 'Официальные условия monobank' : 'Офіційні умови monobank' }} <span aria-hidden="true">↗</span></a>
        </section>
        <section data-terms-provider="privat" hidden>
            <div class="installment-terms-dialog__brand"><img src="{{ Vite::asset('bona-html/privatbank-chastyny.svg') }}" alt="ПриватБанк"><strong>{{ $isRussian ? 'ПриватБанк · Оплата частями' : 'ПриватБанк · Оплата частинами' }}</strong></div>
            <ol><li>{{ $isRussian ? 'Добавьте комплект в корзину и выберите ПриватБанк при оформлении.' : 'Додайте комплект у кошик і оберіть ПриватБанк під час оформлення.' }}</li><li>{{ $isRussian ? 'Для оплаты нужен доступный лимит сервиса «Оплата частями».' : 'Для оплати потрібен доступний ліміт сервісу «Оплата частинами».' }}</li><li>{{ $isRussian ? 'Перед подтверждением банк покажет окончательный график и условия платежей.' : 'Перед підтвердженням банк покаже остаточний графік та умови платежів.' }}</li></ol>
            <a href="https://privatbank.ua/kredyty/oplata-chastynamy-ta-myttyeva-rozstrochka" target="_blank" rel="noopener noreferrer">{{ $isRussian ? 'Официальные условия ПриватБанка' : 'Офіційні умови ПриватБанку' }} <span aria-hidden="true">↗</span></a>
        </section>
        <p class="installment-terms-dialog__note">{{ $isRussian ? 'Доступность программы, лимит и окончательные условия определяет банк.' : 'Доступність програми, ліміт та остаточні умови визначає банк.' }}</p>
    </dialog>
@endif

<x-store.call-consultation-modal :options="$applicationGlobalOptions" />
