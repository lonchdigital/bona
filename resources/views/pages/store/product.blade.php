@extends('layouts.store-main')

@php
    $currentProduct = $product;
    $productUrl = url()->current();
    $catalogUrl = App\Helpers\MultiLangRoute::getMultiLangRoute('store.catalog.page', ['productTypeSlug' => $product->productType->slug]);
    $localizeBlockValue = static function ($value) {
        if (! is_array($value)) {
            return trim((string) $value);
        }

        return trim((string) ($value[app()->getLocale()] ?? collect($value)->first(fn ($item) => filled($item)) ?? ''));
    };

    $productDescriptionHtml = trim((string) ($productText['content'] ?? ''));
    $productShortHtml = trim((string) ($productText['short_content'] ?? ''));
    $productDescription = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($productDescriptionHtml ?: $productShortHtml))));
    $productImages = collect([$product->main_image_path])
        ->merge($productGallery->pluck('image_path'))
        ->filter()
        ->map(fn ($path) => url(\Illuminate\Support\Facades\Storage::url($path)))
        ->unique()
        ->values()
        ->all();
    $availabilityMap = [
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_STOCK => 'https://schema.org/InStock',
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_ORDER => 'https://schema.org/BackOrder',
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_OF_STOCK => 'https://schema.org/OutOfStock',
        \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_ASK_MANAGER => 'https://schema.org/LimitedAvailability',
    ];
    $availability = \App\DataClasses\ProductStatusDataClass::get($product->availability_status_id);
    $faqItems = collect($productFaqs ?? [])->filter(fn ($item) => filled(strip_tags((string) $item->question)) && filled(strip_tags((string) $item->answer)));
    $seoSectionTitle = trim((string) data_get($productSeoData, 'title.'.app()->getLocale(), ''));
    $seoSectionContent = trim((string) data_get($productSeoData, 'content.'.app()->getLocale(), ''));
    $preparedContentBlocks = collect($productContentBlocks ?? [])->map(function ($block) use ($localizeBlockValue) {
        $block = is_array($block) ? $block : [];
        $block['_eyebrow'] = $localizeBlockValue($block['eyebrow'] ?? '');
        $block['_title'] = $localizeBlockValue($block['title'] ?? '');
        $block['_content'] = $localizeBlockValue($block['content'] ?? '');
        $block['_quote'] = $localizeBlockValue($block['quote'] ?? '');
        $block['_author'] = $localizeBlockValue($block['author'] ?? '');
        $block['_button_label'] = $localizeBlockValue($block['button_label'] ?? '');
        $block['_image_url'] = filled($block['image_path'] ?? null) ? \Illuminate\Support\Facades\Storage::url($block['image_path']) : null;
        $block['_items'] = collect($block['items'] ?? [])->map(fn ($item) => [
            'title' => $localizeBlockValue($item['title'] ?? ''),
            'text' => $localizeBlockValue($item['text'] ?? ''),
        ])->filter(fn ($item) => filled($item['title']) || filled($item['text']))->values();

        return $block;
    })->filter(function ($block) {
        return filled(strip_tags($block['_title']))
            || filled(strip_tags($block['_content']))
            || filled(strip_tags($block['_quote']))
            || filled($block['_image_url'])
            || $block['_items']->isNotEmpty();
    })->values();
    $schemaFlags = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG;
@endphp

@section('body_class', 'bona-content-body')
@section('seo_title', $product->meta_title ?: $product->name.' — '.trans('base.site_title'))
@section('meta_description', $product->meta_description ?: $productDescription)
@section('meta_keywords', $product->meta_keywords ?: '')
@section('og_type', 'product')
@section('og_title', $product->name.' — '.trans('base.site_title'))
@section('og_description', $product->meta_description ?: $productDescription)
@if($product->main_image_url)
    @section('og_image', $product->main_image_url)
    @section('og_image_alt', $product->name)
@endif

@push('head')
    @if($product->meta_tags){!! $product->meta_tags !!}@endif
@endpush

@push('structured_data')
    <script type="application/ld+json">{!! json_encode(array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        '@id' => $productUrl.'#product',
        'name' => (string) $product->name,
        'url' => $productUrl,
        'sku' => $product->sku ?: null,
        'image' => $productImages ?: null,
        'description' => $productDescription !== '' ? \Illuminate\Support\Str::limit($productDescription, 900) : null,
        'category' => (string) $product->productType->name,
        'brand' => $product->brand ? ['@type' => 'Brand', 'name' => (string) $product->brand->name] : null,
        'aggregateRating' => $productRatingSummary ? [
            '@type' => 'AggregateRating',
            'ratingValue' => $productRatingSummary['average'],
            'reviewCount' => $productRatingSummary['count'],
            'bestRating' => $productRatingSummary['best'],
            'worstRating' => $productRatingSummary['worst'],
        ] : null,
        'review' => $productReviews->take(10)->map(fn ($item) => [
            '@type' => 'Review',
            'author' => ['@type' => 'Person', 'name' => $item->author_name],
            'datePublished' => optional($item->publishedDate())->toDateString(),
            'reviewRating' => ['@type' => 'Rating', 'ratingValue' => $item->rating, 'bestRating' => 5, 'worstRating' => 1],
            'reviewBody' => $item->review,
        ])->values()->all() ?: null,
        'offers' => is_numeric($product->price) && (float) $product->price > 0 ? array_filter([
            '@type' => 'Offer',
            'url' => $productUrl,
            'price' => (string) $product->price,
            'priceCurrency' => $baseCurrency->code ?: 'UAH',
            'availability' => $availabilityMap[$product->availability_status_id] ?? null,
            'itemCondition' => 'https://schema.org/NewCondition',
            'seller' => ['@id' => app(\App\Services\Seo\OrganizationSchemaService::class)->organizationId()],
        ]) : null,
    ]), $schemaFlags) !!}</script>
    <script type="application/ld+json">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => trans('base.home'), 'item' => url(App\Helpers\MultiLangRoute::getMultiLangRoute('store.home'))],
            ['@type' => 'ListItem', 'position' => 2, 'name' => (string) $product->productType->name, 'item' => url($catalogUrl)],
            ['@type' => 'ListItem', 'position' => 3, 'name' => (string) $product->name, 'item' => $productUrl],
        ],
    ], $schemaFlags) !!}</script>
    @if($faqItems->isNotEmpty())
        <script type="application/ld+json">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $faqItems->map(fn ($item) => [
                '@type' => 'Question',
                'name' => trim(strip_tags((string) $item->question)),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => (string) $item->answer],
            ])->values()->all(),
        ], $schemaFlags) !!}</script>
    @endif
@endpush

@section('content')
    <div class="bona-content-page bona-product-page product">
        <x-store.content-breadcrumbs :items="[
            ['label' => $product->productType->name, 'url' => $catalogUrl],
            ['label' => $product->name],
        ]" />

        <section class="bona-product-hero">
            <div class="bona-shell bona-product-hero__grid">
                <div class="bona-product-gallery art-single-product-gallery">
                    <div class="art-gallery-all-slides-container d-none" aria-hidden="true">
                        <div class="art-swiper-single-wallpaper">
                            @if($product->main_image_url)
                                <div class="swiper-slide" data-color-id="0">
                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $product->main_image_url }}"><img src="{{ $product->main_image_url }}" alt="{{ $product->name }}"></a>
                                </div>
                            @endif
                            @foreach($productGallery as $image)
                                <div class="swiper-slide" data-color-id="{{ $image->color_id ?? 0 }}">
                                    <a data-fancybox="single-wallpaper-gallery" href="{{ $image->gallery_image_url }}"><img src="{{ $image->gallery_image_url }}" alt="{{ $product->name }}"></a>
                                </div>
                            @endforeach
                        </div>
                        <div class="art-swiper-single-wallpaper-thumbs">
                            @if($product->main_image_url)
                                <div class="swiper-slide" data-color-id="0"><div class="art-swiper-slide"><img src="{{ $product->main_image_url }}" alt="{{ $product->name }}"></div></div>
                            @endif
                            @foreach($productGallery as $image)
                                <div class="swiper-slide" data-color-id="{{ $image->color_id ?? 0 }}"><div class="art-swiper-slide"><img src="{{ $image->gallery_image_url }}" alt="{{ $product->name }}"></div></div>
                            @endforeach
                        </div>
                    </div>

                    @if($product->main_image_url)
                        <div class="swiper-single-wallpaper-wrap">
                            <div class="swiper-single-wallpaper">
                                <div class="swiper-wrapper"></div>
                                <div class="swiper-button-next" aria-label="{{ app()->getLocale() === 'ru' ? 'Следующее изображение' : 'Наступне зображення' }}"></div>
                                <div class="swiper-button-prev" aria-label="{{ app()->getLocale() === 'ru' ? 'Предыдущее изображение' : 'Попереднє зображення' }}"></div>
                            </div>
                            <div class="swiper-pagination d-sm-none"></div>
                        </div>
                        <div class="swiper-single-wallpaper-thumbs-wrap d-sm-flex">
                            <div class="swiper-pagination"></div>
                            <div class="art-single-wallpaper-thumbs-wrapper">
                                <div class="swiper-single-wallpaper-thumbs swiper"><div class="swiper-wrapper"></div></div>
                            </div>
                        </div>
                    @else
                        <div class="bona-product-gallery__empty"><img src="{{ asset('assets/images/no-image.png') }}" alt="{{ $product->name }}"></div>
                    @endif
                </div>

                <div class="bona-product-buybox">
                    <div class="bona-product-buybox__topline">
                        @if($availability && $product->availability_status_id !== \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_NONE)
                            <span class="bona-product-status bona-product-status--{{ $product->availability_status_id }}">{{ $availability['name'] }}</span>
                        @endif
                        <div class="bona-product-actions">
                            <button type="button"
                                    class="link-heart product-wish-list-button single-product-wish-list{{ collect($wishListProducts ?? [])->contains($product->id) ? ' link-heart-active' : '' }}"
                                    id="{{ $product->slug }}"
                                    aria-label="{{ trans('base.add_to_wish_list') }}"
                                    aria-pressed="{{ collect($wishListProducts ?? [])->contains($product->id) ? 'true' : 'false' }}">
                                <x-wish-heart />
                            </button>
                            <button class="bona-pdp-compare" type="button" aria-label="{{ trans('base.add_to_compare') }}" aria-pressed="false"
                                    data-product-compare data-product-slug="{{ $product->slug }}"
                                    data-add-label="{{ trans('base.add_to_compare') }}" data-remove-label="{{ trans('base.remove_from_compare') }}">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 7h14"></path><path d="m16 4 3 3-3 3"></path><path d="M19 17H5"></path><path d="m8 14-3 3 3 3"></path></svg>
                            </button>
                        </div>
                    </div>

                    @if($product->brand)
                        <a class="bona-product-buybox__brand" href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.brand.page', ['brandSlug' => $product->brand->slug]) }}">{{ $product->brand->name }}</a>
                    @endif
                    <h1>{{ $product->name }}</h1>
                    @if(filled(strip_tags($productShortHtml)))
                        <div class="bona-product-buybox__intro bona-content-richtext">{!! $productShortHtml !!}</div>
                    @endif

                    <dl class="bona-product-meta">
                        @if($product->sku)<div><dt>{{ trans('base.sku') }}</dt><dd>{{ $product->sku }}</dd></div>@endif
                        @foreach($product->productType->fields->where('as_image', '!=', true)->where('display_on_single', true) as $customField)
                            @php
                                $rawFieldValue = $product->getCustomFieldValue($customField->id);
                                $displayFieldValue = null;
                                if ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING) {
                                    $displayFieldValue = is_array($rawFieldValue) ? implode(', ', $rawFieldValue) : $rawFieldValue;
                                } elseif ($customField->field_type_id === \App\DataClasses\ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION) {
                                    $ids = is_array($rawFieldValue) ? $rawFieldValue : [$rawFieldValue];
                                    $displayFieldValue = $customField->options->whereIn('id', array_filter($ids))->pluck('name')->filter()->implode(', ');
                                }
                            @endphp
                            @if(filled($displayFieldValue))<div><dt>{{ $customField->field_name }}</dt><dd>{{ $displayFieldValue }}</dd></div>@endif
                        @endforeach
                    </dl>

                    <div class="bona-product-options">
                        @foreach($attributeOptions as $id => $allOptions)
                            @foreach($allOptions as $name => $options)
                                @if(count($options))
                                    <label class="bona-product-option">
                                        <span>{{ $name }}</span>
                                        <select name="option" id="option-id-{{ $id }}" class="art-select-attribute">
                                            <option value="">— {{ trans('base.select') }} —</option>
                                            @foreach($options as $item)
                                                <option value="{{ json_encode(['id' => $item['id'], 'name' => $item->getRawOriginal('name')]) }}" data-price="{{ $item['price'] }}">
                                                    {{ $item['name'] }}@if($item['price']) · {{ $item['price'] }} {{ $baseCurrency->name_short }}@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </label>
                                @endif
                            @endforeach
                        @endforeach

                        @if($product->colors->isNotEmpty())
                            <div class="bona-product-option">
                                <span>{{ trans('base.color') }}</span>
                                <div class="art-colors-list bona-product-colors">
                                    @foreach($product->colors as $colorItem)
                                        <span class="color-btn{{ $colorItem->hex === '#fff' ? ' art-white' : '' }}" tabindex="0" role="button"
                                              aria-label="{{ trans('base.catalog_select_color', ['color' => $colorItem->name]) }}"
                                              data-color-id="{{ $colorItem->id }}"
                                              data-name="{{ json_encode(['id' => $colorItem->id, 'name' => $colorItem->getRawOriginal('name')]) }}"
                                              data-price="{{ $colorItem->pivot->price ?? 0 }}"
                                              @if(! $colorItem->display_as_image) style="background-color: {{ $colorItem->hex }};" @endif>
                                            @if($colorItem->display_as_image)<img src="{{ $colorItem->image_url }}" alt="{{ $colorItem->name }}" loading="lazy">@endif
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        @foreach($categoryProducts as $categoryName => $subProducts)
                            @php($subDialogId = 'dialog-content-'.\Illuminate\Support\Str::slug($categoryName).'-'.$loop->index)
                            <div class="bona-product-option sub-product-wrapper">
                                <span>{{ $categoryName }}</span>
                                <button type="button" class="art-dialog-link bona-product-option__picker" data-fancybox data-src="#{{ $subDialogId }}">{{ trans('base.select') }} <span aria-hidden="true">→</span></button>
                                <div class="added-sub-products" data-wrapper="{{ $subDialogId }}"></div>
                                <div id="{{ $subDialogId }}" class="art-popup-single-product bona-subproduct-dialog">
                                    <h2>{{ $categoryName }}</h2>
                                    <div class="art-popup-list-sub-products">
                                        @foreach($subProducts as $subProduct)
                                            <article class="art-product-item">
                                                <div class="art-product-data">
                                                    <a href="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.page', ['productSlug' => $subProduct->slug]) }}" class="art-product-link">
                                                        <div class="image"><img src="{{ $subProduct->preview_image_url }}" alt="{{ $subProduct->name }}" loading="lazy"></div>
                                                        <div class="text"><h3 class="product-title">{{ $subProduct->name }}</h3><span class="price-wrapper"><span class="price">{{ $subProduct->price }}</span> <span class="currency">{{ $baseCurrency->name_short }}</span></span></div>
                                                    </a>
                                                    <button type="button" class="btn single-sub-product-add-to-cart" data-count="0" data-added="0" data-id="{{ $subProduct->id }}" data-slug="{{ $subProduct->slug }}">{{ trans('base.select') }}</button>
                                                </div>
                                            </article>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="bona-product-purchase">
                        @if(is_numeric($product->price) && (float) $product->price > 0)
                            <div class="bona-product-price">
                                <div><span id="product-price" data-count="1" data-start-price="{{ $product->price }}" data-product-price="{{ $product->price }}">{{ $product->price }}</span> <span>{{ $baseCurrency->name_short }}</span></div>
                                @if((float) $product->old_price > (float) $product->price)<del>{{ $product->old_price }} {{ $baseCurrency->name_short }}</del>@endif
                                <small>{{ trans('base.product_cost_description') }}</small>
                            </div>
                        @else
                            <span id="product-price" data-count="1" data-start-price="0" data-product-price="0" hidden>0</span>
                        @endif

                        @if(! in_array($product->availability_status_id, [\App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_OUT_OF_STOCK, \App\DataClasses\ProductStatusDataClass::PRODUCT_STATUS_ORDER], true) && is_numeric($product->price) && (float) $product->price > 0)
                            <div class="bona-product-purchase__controls">
                                <div id="count-of-products-body" class="custom-control-number">
                                    <button type="button" class="counter minus" aria-label="−"></button>
                                    <input type="number" id="count-of-products" min="1" value="1" aria-label="{{ trans('base.quantity') }}">
                                    <button type="button" class="counter plus" aria-label="+"></button>
                                </div>
                                <button type="button" class="bona-button bona-button--dark single-product-add-to-cart" id="{{ $product->slug }}">{{ trans('base.add_to_cart') }}</button>
                            </div>
                            <button type="button" class="bona-product-secondary-action btn-one-click" data-fancybox data-src="#dialog-buy-one-click">{{ trans('base.buy_in_one_click') }} <span aria-hidden="true">→</span></button>
                        @else
                            <button type="button" class="bona-button bona-button--dark" data-fancybox data-src="#order-request">{{ trans('base.leave_request') }}</button>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        @if($characteristics->isNotEmpty() || filled(strip_tags($productDescriptionHtml)))
            <section class="bona-product-core">
                <div class="bona-shell bona-product-core__grid">
                    @if(filled(strip_tags($productDescriptionHtml)))
                        <div class="bona-product-description">
                            <p class="bona-content-kicker">{{ trans('base.description') }}</p>
                            <div class="bona-content-richtext">{!! $productDescriptionHtml !!}</div>
                        </div>
                    @endif
                    @if($characteristics->isNotEmpty())
                        <div class="bona-product-specs">
                            <p class="bona-content-kicker">{{ trans('base.characteristics') }}</p>
                            <dl>
                                @foreach($characteristics as $characteristic)
                                    @if(filled($characteristic['name']) || filled($characteristic['value']))
                                        <div><dt>{{ $characteristic['name'] }}</dt><dd>{{ $characteristic['value'] }}</dd></div>
                                    @endif
                                @endforeach
                            </dl>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        @foreach($preparedContentBlocks as $block)
            @if(($block['type'] ?? '') === 'text')
                <section class="bona-product-editorial bona-product-editorial--text">
                    <div class="bona-shell bona-product-editorial__text">
                        @if($block['_eyebrow'])<p class="bona-content-kicker">{{ $block['_eyebrow'] }}</p>@endif
                        @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif
                        @if(filled(strip_tags($block['_content'])))<div class="bona-content-richtext">{!! $block['_content'] !!}</div>@endif
                    </div>
                </section>
            @elseif(($block['type'] ?? '') === 'image_text')
                <section class="bona-product-editorial bona-product-editorial--split">
                    <div class="bona-shell bona-product-editorial__split{{ ($block['image_position'] ?? 'left') === 'right' ? ' is-reverse' : '' }}">
                        @if($block['_image_url'])<figure><img src="{{ $block['_image_url'] }}" alt="{{ $block['_title'] ?: $product->name }}" width="900" height="700" loading="lazy" decoding="async"></figure>@endif
                        <div>
                            @if($block['_eyebrow'])<p class="bona-content-kicker">{{ $block['_eyebrow'] }}</p>@endif
                            @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif
                            @if(filled(strip_tags($block['_content'])))<div class="bona-content-richtext">{!! $block['_content'] !!}</div>@endif
                            @if($block['_button_label'] && filled($block['button_url'] ?? null))<a class="bona-button bona-button--dark" href="{{ $block['button_url'] }}">{{ $block['_button_label'] }}</a>@endif
                        </div>
                    </div>
                </section>
            @elseif(($block['type'] ?? '') === 'features' && $block['_items']->isNotEmpty())
                <section class="bona-product-editorial bona-product-editorial--features">
                    <div class="bona-shell">
                        @if($block['_eyebrow'])<p class="bona-content-kicker">{{ $block['_eyebrow'] }}</p>@endif
                        @if($block['_title'])<h2>{{ $block['_title'] }}</h2>@endif
                        <div class="bona-product-features">
                            @foreach($block['_items'] as $item)
                                <article><span>{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>@if($item['title'])<h3>{{ $item['title'] }}</h3>@endif @if($item['text'])<p>{{ $item['text'] }}</p>@endif</article>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif(($block['type'] ?? '') === 'quote' && filled(strip_tags($block['_quote'])))
                <section class="bona-product-editorial bona-product-editorial--quote">
                    <div class="bona-shell"><blockquote>{!! $block['_quote'] !!}</blockquote>@if($block['_author'])<cite>{{ $block['_author'] }}</cite>@endif</div>
                </section>
            @endif
        @endforeach

        @if($productVideos->isNotEmpty())
            <section class="bona-product-videos" aria-labelledby="product-videos-title">
                <div class="bona-shell">
                    <header class="bona-content-heading"><div><p class="bona-content-kicker">Video</p><h2 id="product-videos-title">{{ trans('base.open_systems') }}</h2></div></header>
                    <div class="bona-product-videos__grid">
                        @foreach($productVideos as $video)
                            @if(filled((string) $video->iframe))
                                <article>@if($video->tab)<h3>{{ $video->tab }}</h3>@endif<div class="bona-product-video-frame">{!! $video->iframe !!}</div></article>
                            @endif
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($faqItems->isNotEmpty())
            <section class="bona-product-faq" aria-labelledby="product-faq-title">
                <div class="bona-shell bona-product-faq__grid">
                    <header><p class="bona-content-kicker">FAQ</p><h2 id="product-faq-title">{{ app()->getLocale() === 'ru' ? 'Вопросы о модели' : 'Питання про модель' }}</h2></header>
                    <div>
                        @foreach($faqItems as $faq)
                            <details><summary>{{ $faq->question }}<span aria-hidden="true">+</span></summary><div class="bona-content-richtext">{!! $faq->answer !!}</div></details>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="bona-product-reviews" id="product-reviews" aria-labelledby="product-reviews-title">
            <div class="bona-shell bona-product-reviews__grid">
                <header>
                    <p class="bona-content-kicker">Bona Doors</p>
                    <h2 id="product-reviews-title">{{ trans('base.product_reviews_title') }}</h2>
                    @if($productRatingSummary)<p class="bona-product-reviews__rating"><strong>{{ $productRatingSummary['average'] }}</strong>/5 · {{ trans('base.product_review_based_on', ['COUNT' => $productRatingSummary['count']]) }}</p>@endif
                    <button type="button" class="bona-button bona-button--dark art-product-reviews__open" data-fancybox data-src="#dialog-product-review">{{ trans('base.product_review_leave') }}</button>
                </header>
                <div>
                    @if(Session::has('review_success'))<div class="bona-notice bona-notice--success">{{ Session::get('review_success') }}</div>@endif
                    @if(Session::has('review_error'))<div class="bona-notice bona-notice--error">{{ Session::get('review_error') }}</div>@endif
                    @if($errors->any())<div class="bona-notice bona-notice--error"><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
                    @if($productReviews->isEmpty())
                        <p class="bona-product-reviews__empty">{{ trans('base.product_reviews_empty') }}</p>
                    @else
                        <ol class="bona-product-reviews__list">
                            @foreach($productReviews as $review)
                                <li><div><strong>{{ $review->author_name }}</strong><span>{{ $review->rating }}/5</span><time datetime="{{ optional($review->publishedDate())->toDateString() }}">{{ optional($review->publishedDate())->translatedFormat('d F Y') }}</time></div><p>{{ $review->review }}</p></li>
                            @endforeach
                        </ol>
                    @endif
                </div>
            </div>
        </section>

        @if($seoSectionTitle || filled(strip_tags($seoSectionContent)))
            <section class="bona-product-seo">
                <div class="bona-shell bona-product-seo__inner">
                    @if($seoSectionTitle)<h2>{{ $seoSectionTitle }}</h2>@endif
                    @if(filled(strip_tags($seoSectionContent)))<div class="bona-content-richtext">{!! $seoSectionContent !!}</div>@endif
                </div>
            </section>
        @endif

        @if(count($sameTypeProducts))
            <section class="bona-product-related products" aria-labelledby="related-products-title">
                <div class="bona-shell">
                    <header class="bona-content-heading"><div><p class="bona-content-kicker">Bona Doors</p><h2 id="related-products-title">{{ trans('base.see_more') }}</h2></div></header>
                    <div class="art-products-slider-wrapper art-big-wrapper art-carousel">
                        <div class="swiper art-products-owl-items art-big-wrapper art-swiper-common"><div class="swiper-wrapper">
                            @foreach($sameTypeProducts as $relatedProduct)
                                <div class="swiper-slide">@include('pages.store.partials.product_item', ['product' => $relatedProduct, 'baseCurrency' => $baseCurrency])</div>
                            @endforeach
                        </div><div class="swiper-pagination"></div></div>
                    </div>
                </div>
            </section>
        @endif
    </div>

    <div id="order-request" class="art-popup-call-measurer bona-form-dialog">
        <form action="#" id="order-request-form" method="post" class="art-contact-form art-order-form">
            @csrf
            <h2 class="title h2">{{ trans('base.leave_request') }}</h2>
            <p>{{ trans('base.call_measurer_description') }}</p>
            <div class="art-fields-row"><input type="text" class="art-light-field name-field" name="name" placeholder="{{ trans('base.name') }}"><input type="tel" class="art-light-field phone-field" name="phone" placeholder="{{ trans('base.phone') }}"></div>
            <input type="hidden" name="agree" value="1"><input type="hidden" name="event" value="submit_form_order_count">
            <button type="submit" class="bona-button bona-button--light">{{ trans('base.send') }}</button>
            <a href="{{ url()->current() }}" class="d-none art-current-product-link">{{ $product->name }}</a>
        </form>
    </div>

    <div id="dialog-buy-one-click" class="art-popup-call-measurer bona-form-dialog">
        <form action="{{ App\Helpers\MultiLangRoute::getMultiLangRoute('store.product.one-click-order', ['productSlug' => $currentProduct->slug]) }}" id="one-click-order-form" method="post" class="art-contact-form">
            @csrf
            <h2>{{ trans('base.buy_in_one_click') }}</h2>
            <p>{{ trans('base.buy_one_click_description') }}</p>
            <div class="art-fields-row"><input type="text" class="art-light-field name-field" name="name" placeholder="{{ trans('base.name') }}" required><input type="tel" class="art-light-field phone-field" name="phone" placeholder="{{ trans('base.phone') }}" inputmode="tel" required></div>
            <input type="hidden" name="agree" value="1"><input type="hidden" name="event" value="submit_form_buy_one_click">
            <button type="submit" class="bona-button bona-button--light">{{ trans('base.buy_one_click_submit') }}</button>
        </form>
    </div>

    <div id="dialog-product-review" class="art-popup-call-measurer bona-form-dialog">
        <form action="{{ route('store.product-review.submit') }}" method="post" class="art-contact-form art-review-form">
            @csrf
            <input type="hidden" name="product_id" value="{{ $product->id }}">
            <h2>{{ trans('base.product_review_leave') }}</h2>
            <p>{{ trans('base.product_review_about_hint') }}</p>
            <div class="art-fields-row"><label>{{ trans('base.product_review_rating') }}<select name="rating" class="art-light-field" required>@foreach([5,4,3,2,1] as $rating)<option value="{{ $rating }}" @selected(old('rating', 5) == $rating)>{{ $rating }} / 5</option>@endforeach</select></label><label>{{ trans('base.name') }}<input type="text" class="art-light-field" name="author_name" value="{{ old('author_name') }}" required></label></div>
            <label>{{ trans('base.email') }}<input type="email" class="art-light-field" name="author_email" value="{{ old('author_email') }}" required></label>
            <label>{{ trans('base.product_review_text') }}<textarea class="art-light-field" name="review" rows="5" required>{{ old('review') }}</textarea></label>
            <div class="art-review-form__trap" aria-hidden="true"><label>Website<input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
            <button type="submit" class="bona-button bona-button--light">{{ trans('base.send') }}</button>
        </form>
    </div>
@endsection

@push('dynamic_scripts')
    @if($errors->any() || Session::has('review_error'))
        <script>document.addEventListener('DOMContentLoaded', function () { document.querySelector('.art-product-reviews__open')?.click(); });</script>
    @endif
@endpush
