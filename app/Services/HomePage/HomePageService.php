<?php

namespace App\Services\HomePage;

use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Helpers\MultiLangRoute;
use App\Models\Category;
use App\Models\Faqs;
use App\Models\HomePageBestSalesProducts;
use App\Models\HomePageBrands;
use App\Models\HomePageConfig;
use App\Models\HomePageNewProducts;
use App\Models\HomePageProductOptions;
use App\Models\HomePageSlides;
use App\Models\HomePageTestimonials;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SeoText;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\HomePage\DTO\HomePageEditDTO;
use App\Services\Instagram\InstagramFeedService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class HomePageService extends BaseService
{
    const HOME_PAGE_IMAGES_FOLDER = 'home-page-images';

    const STYLE_IMAGES_FOLDER = 'home-page-style-images';

    const CONTENT_IMAGES_FOLDER = 'home-page-content-images';

    private const CONTENT_IMAGE_ASSETS = [
        'bedroom' => 'bona-html/img/interior-bedroom.jpg',
        'living' => 'bona-html/img/interior-living.jpg',
        'hall' => 'bona-html/img/interior-hall.jpg',
        'apartment' => 'bona-html/img/interior-apartment.jpg',
        'house' => 'bona-html/img/interior-house.jpg',
        'office' => 'bona-html/img/interior-office.jpg',
    ];

    public function editHomePage(HomePageEditDTO $request): ServiceActionResult
    {
        //        dd($request->slides);
        return $this->coverWithDBTransaction(function () use ($request) {

            $homePageConfig = $this->getHomePageConfig();
            $styleSection = $this->syncStyleSection($request->styleSection, $homePageConfig?->style_section ?? []);
            $contentSections = $this->syncContentSections(
                $request->contentSections,
                $homePageConfig?->content_sections ?? [],
            );
            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
                'product_types' => json_encode($request->selectedProductTypes ?? []),
                'style_section' => $styleSection,
                'content_sections' => $contentSections,
            ];

            if ($homePageConfig) {
                $homePageConfig->update($dataToUpdate);
            } else {
                HomePageConfig::create($dataToUpdate);
            }

            $this->syncSlides($request->slides);
            $this->syncTestimonials($request->testimonials);
            $this->syncFaqs(config('constants.HOMEPAGE_TYPE'), $request->faqs);
            [$seoTitle, $seoText] = $this->preserveExistingSeoText(
                $request->seoTitle,
                $request->seoText,
            );

            SeoText::updateSeoText(
                config('constants.HOMEPAGE_TYPE'),
                $seoTitle,
                $seoText,
            );

            $this->syncNewProducts($request->selectedProductsId);
            $this->syncBestSalesProducts($request->selectedBestSalesProductsId);
            $this->syncBrands($request->selectedBrandsId);

            return ServiceActionResult::make(true, trans('admin.home_page_edit_success'));
        });
    }

    /**
     * The home form contains several independent editable sections. A rich
     * text editor that has not mounted yet can submit an empty value while an
     * administrator is only changing another section. The homepage SEO copy
     * is business content, so an empty editor payload must not silently erase
     * an existing value.
     */
    private function preserveExistingSeoText(?array $titles, ?array $contents): array
    {
        $existing = $this->getHomePageSeoText();
        $titles ??= [];
        $contents ??= [];

        $languages = collect([
            ...array_keys($existing['title'] ?? []),
            ...array_keys($existing['content'] ?? []),
            ...array_keys($titles),
            ...array_keys($contents),
        ])->unique();

        foreach ($languages as $language) {
            if (! $this->hasVisibleText($titles[$language] ?? null)
                && $this->hasVisibleText($existing['title'][$language] ?? null)) {
                $titles[$language] = $existing['title'][$language];
            }

            if (! $this->hasVisibleText($contents[$language] ?? null)
                && $this->hasVisibleText($existing['content'][$language] ?? null)) {
                $contents[$language] = $existing['content'][$language];
            }
        }

        return [$titles, $contents];
    }

    private function hasVisibleText(?string $value): bool
    {
        $text = html_entity_decode(strip_tags((string) $value), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[\s\x{00A0}]+/u', '', $text);

        return $text !== '';
    }

    private function syncStyleSection(?array $section, array $existingSection): array
    {
        $section ??= [];
        $existingImagePaths = collect($existingSection['items'] ?? [])
            ->pluck('image_path')
            ->filter()
            ->values();

        $items = collect($section['items'] ?? [])->map(function (array $item, int $index) use ($existingImagePaths) {
            $existingPath = trim((string) ($item['existing_image_path'] ?? ''));
            $imagePath = $existingImagePaths->contains($existingPath) ? $existingPath : null;

            if ((bool) ($item['image_deleted'] ?? false)) {
                $imagePath = null;
            }

            if (isset($item['image'])) {
                $imageBasePath = self::STYLE_IMAGES_FOLDER.'/'.Str::uuid();
                $this->storeImage($imageBasePath, $item['image'], 'webp', 82);
                $this->storeImage($imageBasePath, $item['image'], 'jpg', 86);
                $imagePath = $imageBasePath.'.webp';
            }

            return [
                'name' => $this->normalizeTranslations($item['name'] ?? []),
                'image_path' => $imagePath,
                'sort_order' => $index,
            ];
        })->filter(fn (array $item) => $item['image_path'] && collect($item['name'])->contains(fn ($name) => filled($name)))
            ->values();

        $retainedImagePaths = $items->pluck('image_path');
        $existingImagePaths->diff($retainedImagePaths)->each(fn (string $path) => $this->deleteImage($path));

        return [
            'enabled' => (bool) ($section['enabled'] ?? false),
            'kicker' => $this->normalizeTranslations($section['kicker'] ?? []),
            'title' => $this->normalizeTranslations($section['title'] ?? []),
            'description' => $this->normalizeTranslations($section['description'] ?? []),
            'cta_label' => $this->normalizeTranslations($section['cta_label'] ?? []),
            'cta_url' => trim((string) ($section['cta_url'] ?? '')),
            'items' => $items->all(),
        ];
    }

    private function normalizeTranslations(array $translations): array
    {
        return collect(['uk', 'ru'])
            ->mapWithKeys(fn (string $locale) => [$locale => trim((string) ($translations[$locale] ?? ''))])
            ->all();
    }

    public function getHomePageStyleSection(): array
    {
        $section = $this->getHomePageConfig()?->style_section ?? [];
        $section['items'] = collect($section['items'] ?? [])->map(function (array $item) {
            $item['image_url'] = filled($item['image_path'] ?? null)
                ? Storage::url($item['image_path'])
                : null;

            return $item;
        })->sortBy('sort_order')->values()->all();

        return $section;
    }

    /**
     * Existing installations receive the reference copy as a non-destructive
     * default. Once the homepage editor is saved, this JSON configuration is
     * the source of truth for every redesigned content section.
     */
    public function getHomePageContentSections(): array
    {
        $defaults = $this->defaultContentSections();
        $stored = $this->getHomePageConfig()?->content_sections ?? [];

        return collect($defaults)->mapWithKeys(function (array $defaultSection, string $sectionKey) use ($stored) {
            $storedSection = is_array($stored[$sectionKey] ?? null) ? $stored[$sectionKey] : [];
            $defaultItems = $defaultSection['items'] ?? null;
            $storedHasItems = array_key_exists('items', $storedSection);

            unset($defaultSection['items'], $storedSection['items']);
            $section = array_replace_recursive($defaultSection, $storedSection);

            if ($defaultItems !== null) {
                $section['items'] = $storedHasItems
                    ? (is_array($stored[$sectionKey]['items']) ? $stored[$sectionKey]['items'] : [])
                    : $defaultItems;
            }

            if (in_array($sectionKey, ['ideas', 'works'], true)) {
                $section['items'] = collect($section['items'] ?? [])->map(function (array $item) {
                    $item['image_url'] = $this->contentImageUrl($item);

                    return $item;
                })->sortBy('sort_order')->values()->all();
            }

            return [$sectionKey => $section];
        })->all();
    }

    private function syncContentSections(?array $submittedSections, array $existingSections): array
    {
        if ($submittedSections === null) {
            return $existingSections;
        }

        $result = [];

        foreach ($this->defaultContentSections() as $sectionKey => $defaultSection) {
            $submitted = is_array($submittedSections[$sectionKey] ?? null)
                ? $submittedSections[$sectionKey]
                : null;

            if ($submitted === null) {
                $result[$sectionKey] = $existingSections[$sectionKey] ?? $defaultSection;

                continue;
            }

            $section = [
                'enabled' => (bool) ($submitted['enabled'] ?? false),
            ];

            foreach ($this->contentSectionTranslationFields($sectionKey) as $field) {
                $section[$field] = $this->normalizeTranslations($submitted[$field] ?? []);
            }

            foreach ($this->contentSectionScalarFields($sectionKey) as $field) {
                $section[$field] = trim((string) ($submitted[$field] ?? ''));
            }

            $section['items'] = match ($sectionKey) {
                'numbers' => $this->normalizeNumberItems($submitted['items'] ?? []),
                'steps' => $this->normalizeStepItems($submitted['items'] ?? []),
                'ideas', 'works' => $this->syncVisualItems(
                    $sectionKey,
                    $submitted['items'] ?? [],
                    $existingSections[$sectionKey]['items'] ?? [],
                ),
                default => null,
            };

            if ($section['items'] === null) {
                unset($section['items']);
            }

            $result[$sectionKey] = $section;
        }

        return $result;
    }

    private function normalizeNumberItems(array $items): array
    {
        return collect($items)->map(fn (array $item, int $index) => [
            'value' => trim((string) ($item['value'] ?? '')),
            'label' => $this->normalizeTranslations($item['label'] ?? []),
            'sort_order' => $index,
        ])->filter(fn (array $item) => $item['value'] !== ''
            || collect($item['label'])->contains(fn ($label) => filled($label)))
            ->values()
            ->all();
    }

    private function normalizeStepItems(array $items): array
    {
        return collect($items)->map(fn (array $item, int $index) => [
            'number' => trim((string) ($item['number'] ?? str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT))),
            'title' => $this->normalizeTranslations($item['title'] ?? []),
            'text' => $this->normalizeTranslations($item['text'] ?? []),
            'sort_order' => $index,
        ])->filter(fn (array $item) => collect($item['title'])->contains(fn ($title) => filled($title)))
            ->values()
            ->all();
    }

    private function syncVisualItems(string $sectionKey, array $items, array $existingItems): array
    {
        $existingImagePaths = collect($existingItems)->pluck('image_path')->filter()->values();
        $allowedDefaults = $sectionKey === 'ideas'
            ? collect(['bedroom', 'living', 'hall'])
            : collect(['apartment', 'house', 'office']);

        $normalized = collect($items)->map(function (array $item, int $index) use ($existingImagePaths, $allowedDefaults) {
            $existingPath = trim((string) ($item['existing_image_path'] ?? ''));
            $imagePath = $existingImagePaths->contains($existingPath) ? $existingPath : null;
            $defaultImage = $allowedDefaults->contains($item['default_image'] ?? null)
                ? $item['default_image']
                : null;

            if ((bool) ($item['image_deleted'] ?? false)) {
                $imagePath = null;
                $defaultImage = null;
            }

            if (isset($item['image'])) {
                $imageBasePath = self::CONTENT_IMAGES_FOLDER.'/'.Str::uuid();
                $this->storeImage($imageBasePath, $item['image'], 'webp', 82);
                $this->storeImage($imageBasePath, $item['image'], 'jpg', 86);
                $imagePath = $imageBasePath.'.webp';
                $defaultImage = null;
            }

            return [
                'title' => $this->normalizeTranslations($item['title'] ?? []),
                'text' => $this->normalizeTranslations($item['text'] ?? []),
                'url' => trim((string) ($item['url'] ?? '')),
                'image_path' => $imagePath,
                'default_image' => $defaultImage,
                'sort_order' => $index,
            ];
        })->filter(fn (array $item) => ($item['image_path'] || $item['default_image'])
            && collect($item['title'])->contains(fn ($title) => filled($title)))
            ->values();

        $retainedImagePaths = $normalized->pluck('image_path')->filter();
        $existingImagePaths->diff($retainedImagePaths)->each(fn (string $path) => $this->deleteImage($path));

        return $normalized->all();
    }

    private function contentImageUrl(array $item): ?string
    {
        if (filled($item['image_path'] ?? null)) {
            return Storage::url($item['image_path']);
        }

        $asset = self::CONTENT_IMAGE_ASSETS[$item['default_image'] ?? ''] ?? null;

        return $asset ? Vite::asset($asset) : null;
    }

    private function contentSectionTranslationFields(string $sectionKey): array
    {
        return match ($sectionKey) {
            'hero' => ['eyebrow', 'secondary_label'],
            'catalog', 'numbers', 'ideas', 'faq', 'partners' => ['kicker', 'title'],
            'popular', 'works', 'reviews', 'instagram', 'blog' => ['kicker', 'title', 'link_label'],
            'steps' => ['kicker', 'title', 'cta_label'],
            default => [],
        };
    }

    private function contentSectionScalarFields(string $sectionKey): array
    {
        return match ($sectionKey) {
            'hero' => ['secondary_url'],
            'popular', 'works', 'reviews', 'instagram', 'blog' => ['link_url'],
            'steps' => ['cta_url'],
            default => [],
        };
    }

    private function defaultContentSections(): array
    {
        return [
            'hero' => [
                'enabled' => true,
                'eyebrow' => $this->translations('base.storefront_showroom'),
                'secondary_label' => $this->translations('base.services'),
                'secondary_url' => '',
            ],
            'catalog' => [
                'enabled' => true,
                'kicker' => $this->translations('base.storefront_catalog_kicker'),
                'title' => $this->translations('base.products_by_type'),
            ],
            'popular' => [
                'enabled' => true,
                'kicker' => $this->translations('base.home_popular_kicker'),
                'title' => $this->translations('base.home_popular_title'),
                'link_label' => $this->translations('base.home_all_models'),
                'link_url' => '',
            ],
            'numbers' => [
                'enabled' => true,
                'kicker' => $this->translations('base.home_numbers_kicker'),
                'title' => $this->translations('base.home_numbers_title'),
                'items' => [
                    ['value' => '15', 'label' => $this->translations('base.home_numbers_years'), 'sort_order' => 0],
                    ['value' => '2', 'label' => $this->translations('base.home_numbers_showrooms'), 'sort_order' => 1],
                    ['value' => '3 500+', 'label' => $this->translations('base.home_numbers_installed'), 'sort_order' => 2],
                ],
            ],
            'ideas' => [
                'enabled' => true,
                'kicker' => $this->translations('base.home_ideas_kicker'),
                'title' => $this->translations('base.home_ideas_title'),
                'items' => [
                    $this->defaultVisualItem('bedroom', 'base.home_idea_bedroom_title', 'base.home_idea_bedroom_text', 0),
                    $this->defaultVisualItem('living', 'base.home_idea_living_title', 'base.home_idea_living_text', 1),
                    $this->defaultVisualItem('hall', 'base.home_idea_hall_title', 'base.home_idea_hall_text', 2),
                ],
            ],
            'steps' => [
                'enabled' => true,
                'kicker' => $this->translations('base.home_steps_kicker'),
                'title' => $this->translations('base.home_steps_title'),
                'cta_label' => $this->translations('base.call_measurer'),
                'cta_url' => '#dialog-call-measurer',
                'items' => collect(range(1, 6))->map(fn (int $number) => [
                    'number' => str_pad((string) $number, 2, '0', STR_PAD_LEFT),
                    'title' => $this->translations("base.home_step_{$number}_title"),
                    'text' => $this->translations("base.home_step_{$number}_text"),
                    'sort_order' => $number - 1,
                ])->all(),
            ],
            'works' => [
                'enabled' => true,
                'kicker' => $this->translations('base.home_works_kicker'),
                'title' => $this->translations('base.our_works'),
                'link_label' => $this->translations('base.home_all_projects'),
                'link_url' => '',
                'items' => [
                    $this->defaultVisualItem('apartment', 'base.home_work_apartment_title', 'base.home_work_apartment_text', 0),
                    $this->defaultVisualItem('house', 'base.home_work_house_title', 'base.home_work_house_text', 1),
                    $this->defaultVisualItem('office', 'base.home_work_office_title', 'base.home_work_office_text', 2),
                ],
            ],
            'reviews' => [
                'enabled' => true,
                'kicker' => ['uk' => 'Google Maps', 'ru' => 'Google Maps'],
                'title' => $this->translations('base.client_testimonials'),
                'link_label' => $this->translations('base.google_reviews'),
                'link_url' => (string) config('organization.map_url', ''),
            ],
            'instagram' => [
                'enabled' => true,
                'kicker' => ['uk' => '@bona_doors', 'ru' => '@bona_doors'],
                'title' => $this->translations('base.we_are_in_instagram'),
                'link_label' => [
                    'uk' => trans('base.subscribe', [], 'uk').' на @bona_doors',
                    'ru' => trans('base.subscribe', [], 'ru').' на @bona_doors',
                ],
                'link_url' => 'https://www.instagram.com/bona_doors/',
            ],
            'blog' => [
                'enabled' => true,
                'kicker' => $this->translations('base.blog_latest'),
                'title' => $this->translations('base.blog'),
                'link_label' => $this->translations('base.blog_all'),
                'link_url' => '',
            ],
            'faq' => [
                'enabled' => true,
                'kicker' => $this->translations('base.faqs_subtitle'),
                'title' => $this->translations('base.faqs'),
            ],
            'partners' => [
                'enabled' => true,
                'kicker' => $this->translations('base.partners_kicker'),
                'title' => $this->translations('base.our_partners'),
            ],
            'seo' => [
                'enabled' => true,
            ],
        ];
    }

    private function defaultVisualItem(string $image, string $titleKey, string $textKey, int $sortOrder): array
    {
        return [
            'title' => $this->translations($titleKey),
            'text' => $this->translations($textKey),
            'url' => '',
            'image_path' => null,
            'default_image' => $image,
            'sort_order' => $sortOrder,
        ];
    }

    private function translations(string $key): array
    {
        return [
            'uk' => trans($key, [], 'uk'),
            'ru' => trans($key, [], 'ru'),
        ];
    }

    private function syncNewProducts(?array $productsId): void
    {
        HomePageNewProducts::query()->delete();

        if (! empty($productsId) && $productsId[0] != '') {
            foreach ($productsId as $productId) {
                HomePageNewProducts::create(['product_id' => $productId]);
            }
        }
    }

    private function syncBestSalesProducts(?array $productsId): void
    {
        HomePageBestSalesProducts::query()->delete();

        if (! empty($productsId) && $productsId[0] != '') {
            foreach ($productsId as $productId) {
                HomePageBestSalesProducts::create(['product_id' => $productId]);
            }
        }
    }

    private function syncBrands(?array $brandsId): void
    {
        HomePageBrands::query()->delete();

        foreach (array_filter($brandsId ?? []) as $brandId) {
            HomePageBrands::create(['brand_id' => $brandId]);
        }
    }

    private function syncOptions(array $options): void
    {
        HomePageProductOptions::query()->delete();

        foreach ($options as $optionId) {
            HomePageProductOptions::create(['product_field_option_id' => $optionId]);
        }
    }

    private function syncSlides(?array $slides): void
    {
        $imagesToDelete = [];

        $existingSlides = HomePageSlides::get();
        if ($slides) {
            foreach ($slides as $slide) {
                $dataToUpdate = [
                    'slide_url' => $slide['slide_url'],
                    'title' => $slide['title'],
                    'description' => $slide['description'],
                    'button_text' => $slide['button_text'],
                    'display_button' => $slide['display_button'],
                    'button_url' => $slide['button_url'],
                    // The control is optional, so a slide that predates it keeps
                    // its image untouched rather than picking up a stray value.
                    'overlay_opacity' => (int) ($slide['overlay_opacity'] ?? 0),
                ];

                if (isset($slide['image'])) {
                    $slideImagePath = self::HOME_PAGE_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10);

                    $this->storeImage($slideImagePath, $slide['image'], 'webp');
                    $this->storeImage($slideImagePath, $slide['image'], 'jpg');

                    $dataToUpdate['slide_image_path'] = $slideImagePath.'.webp';
                }
                if (isset($slide['image_mobile'])) {
                    $slideImageMobilePath = self::HOME_PAGE_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10);

                    $this->storeImage($slideImageMobilePath, $slide['image_mobile'], 'webp');
                    $this->storeImage($slideImageMobilePath, $slide['image_mobile'], 'jpg');

                    $dataToUpdate['slide_image_path_mobile'] = $slideImageMobilePath.'.webp';
                }

                if (isset($slide['id']) && $slide['id']) {
                    $existingSlide = $existingSlides->where('id', $slide['id'])->first();
                    if (! $existingSlide) {
                        throw new \Exception('Incorrect slide id: '.$slide['id']);
                    }

                    if (isset($slide['image'])) {
                        $imagesToDelete[] = $existingSlide->slide_image_path;
                    }
                    if (isset($slide['image_mobile'])) {
                        $imagesToDelete[] = $existingSlide->slide_image_path_mobile;
                    }

                    $existingSlide->update($dataToUpdate);
                } else {
                    HomePageSlides::create($dataToUpdate);
                }
            }
        }

        $existingSlidesInRequest = $slides ? array_filter(array_column($slides, 'id'), function ($item) {
            return $item !== null;
        }) : [];

        $slidesToDelete = $existingSlides->whereNotIn('id', $existingSlidesInRequest);

        foreach ($slidesToDelete as $slideToDelete) {
            $imagesToDelete[] = $slideToDelete->slide_image_path;
            $imagesToDelete[] = $slideToDelete->slide_image_path_mobile;
            $slideToDelete->delete();
        }

        foreach ($imagesToDelete as $imageToDelete) {
            $this->deleteImage($imageToDelete);
        }

    }

    private function syncTestimonials(?array $testimonials): void
    {
        $imagesToDelete = [];

        $existingTestimonials = HomePageTestimonials::get();

        if ($testimonials) {
            foreach ($testimonials as $testimonial) {
                $dataToUpdate = [
                    'name' => $testimonial['name'],
                    'review' => $testimonial['review'],
                    'rating' => $testimonial['rating'],
                    'date' => $testimonial['date'],
                    'url' => $testimonial['url'],
                ];

                if (isset($testimonial['image'])) {
                    //                    dd($testimonial['image']);
                    $testimonialImagePath = self::HOME_PAGE_IMAGES_FOLDER.'/'.sha1(time()).'_'.Str::random(10);

                    $this->storeImage($testimonialImagePath, $testimonial['image'], 'webp');
                    $this->storeImage($testimonialImagePath, $testimonial['image'], 'jpg');

                    $dataToUpdate['testimonial_image_path'] = $testimonialImagePath.'.webp';
                }

                if (isset($testimonial['id']) && $testimonial['id']) {
                    $existingTestimonial = $existingTestimonials->where('id', $testimonial['id'])->first();
                    if (! $existingTestimonial) {
                        throw new \Exception('Incorrect testimonial id: '.$testimonial['id']);
                    }

                    if (isset($testimonial['image'])) {
                        $imagesToDelete[] = $existingTestimonial->testimonial_image_path;
                    }

                    $existingTestimonial->update($dataToUpdate);
                } else {
                    HomePageTestimonials::create($dataToUpdate);
                }
            }
        }

        $existingTestimonialsInRequest = $testimonials ? array_filter(array_column($testimonials, 'id'), function ($item) {
            return $item !== null;
        }) : [];

        $testimonialsToDelete = $existingTestimonials->whereNotIn('id', $existingTestimonialsInRequest);

        foreach ($testimonialsToDelete as $testimonialToDelete) {
            $imagesToDelete[] = $testimonialToDelete->testimonial_image_path;
            $testimonialToDelete->delete();
        }

        foreach ($imagesToDelete as $imageToDelete) {
            $this->deleteImage($imageToDelete);
        }

    }

    public function getHomePageConfig(): ?HomePageConfig
    {
        return HomePageConfig::first();
    }

    public function getHomePageNewProducts(): Collection
    {
        return HomePageNewProducts::with(['product'])->get();
    }

    public function getProductTypes(): Collection
    {
        return ProductType::get();
    }

    public function getHomePageCatalogOptions(): Collection
    {
        $productTypes = ProductType::query()
            ->orderBy('id')
            ->get()
            ->map(fn (ProductType $productType) => [
                'id' => (string) $productType->id,
                'name' => $productType->getTranslations('name'),
            ]);

        $categories = Category::query()
            ->with('productType')
            ->orderBy('product_type_id')
            ->orderBy('id')
            ->get()
            ->map(function (Category $category) {
                $names = collect(['uk', 'ru'])->mapWithKeys(function (string $locale) use ($category) {
                    $name = $category->getTranslation('name', $locale, false);
                    $parentName = $category->productType?->getTranslation('name', $locale, false);

                    return [$locale => $parentName ? $name.' — '.$parentName : $name];
                })->all();

                return [
                    'id' => 'category:'.$category->id,
                    'name' => $names,
                ];
            });

        return $productTypes->concat($categories)->values();
    }

    /**
     * Resolve the ordered homepage catalog selection. Numeric values remain
     * backward-compatible product type references; `category:{id}` values let
     * the same admin field link a card to a nested catalog category.
     */
    public function getHomePageCatalogCards(?array $selections): Collection
    {
        $selections = collect($selections ?? [])
            ->map(fn (mixed $selection) => trim((string) $selection))
            ->filter()
            ->unique()
            ->values();

        $productTypeIds = $selections
            ->filter(fn (string $selection) => ctype_digit($selection))
            ->map(fn (string $selection) => (int) $selection);
        $categoryIds = $selections
            ->map(fn (string $selection) => preg_match('/^category:(\d+)$/', $selection, $matches) ? (int) $matches[1] : null)
            ->filter();

        $productTypes = ProductType::query()
            ->whereIn('id', $productTypeIds)
            ->get()
            ->keyBy(fn (ProductType $productType) => (string) $productType->id);
        $categories = Category::query()
            ->with('productType')
            ->whereIn('id', $categoryIds)
            ->get()
            ->keyBy(fn (Category $category) => (string) $category->id);

        return $selections->map(function (string $selection) use ($productTypes, $categories) {
            if (ctype_digit($selection)) {
                $productType = $productTypes->get($selection);

                return $productType ? [
                    'name' => $productType->name,
                    'url' => MultiLangRoute::getMultiLangRoute('store.catalog.page', [
                        'productTypeSlug' => $productType->slug,
                    ]),
                    'image_url' => $productType->image_url,
                ] : null;
            }

            preg_match('/^category:(\d+)$/', $selection, $matches);
            $category = isset($matches[1]) ? $categories->get($matches[1]) : null;

            if (! $category?->productType) {
                return null;
            }

            return [
                'name' => $category->name,
                'url' => MultiLangRoute::getMultiLangRoute('store.catalog-category.page', [
                    'productTypeSlug' => $category->productType->slug,
                    'categorySlug' => $category->slug,
                ]),
                'image_url' => $category->image_url ?: $category->productType->image_url,
            ];
        })->filter()->values();
    }

    public function getSpecificProductTypes(): Collection
    {
        return ProductType::whereNotNull('code_name')->with(['categories'])->get();
    }

    public function getHomePageBestSalesProducts(): Collection
    {
        return HomePageBestSalesProducts::with(['product'])->get();
    }

    /**
     * Products selected as best sellers in the homepage editor are the source
     * of truth for the redesigned "Popular models" block. Older installations
     * often have that list empty, so keep the section useful with recent,
     * available door products until an editor makes a manual selection.
     */
    public function getHomePagePopularProducts(int $limit = 12): Collection
    {
        $relations = ['product.brand', 'product.productType', 'product.colors'];

        $products = HomePageBestSalesProducts::with($relations)
            ->get()
            ->pluck('product')
            ->filter();

        if ($products->isEmpty()) {
            $products = HomePageNewProducts::with($relations)
                ->get()
                ->pluck('product')
                ->filter();
        }

        if ($products->isNotEmpty()) {
            return $products->take($limit)->values();
        }

        return Product::query()
            ->with(['brand', 'productType', 'colors'])
            ->whereNotNull('main_image_path')
            ->whereNotNull('price')
            ->where('price', '>', 0)
            ->where('availability_status_id', ProductStatusDataClass::PRODUCT_STATUS_STOCK)
            ->whereHas('productType', fn ($query) => $query->where('has_size', true))
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->limit($limit)
            ->get();
    }

    public function getHomePageBrands(): Collection
    {
        return HomePageBrands::with(['brand'])->get();
    }

    public function getHomePageProductFieldOptions(): Collection
    {
        return HomePageProductOptions::with(['option'])->get();
    }

    public function getHomePageSlides(): Collection
    {
        return HomePageSlides::get();
    }

    public function getHomePageTestimonials(): Collection
    {
        return HomePageTestimonials::get();
    }

    public function getHomePageFaqs(): Collection
    {
        return Faqs::where('page_type', config('constants.HOMEPAGE_TYPE'))->get();
    }

    public function getHomePageSeoText(): array
    {
        $result = SeoText::where('page_type', config('constants.HOMEPAGE_TYPE'))->get();
        $data = [];

        foreach ($result as $value) {
            $data['title'][$value['language']] = $value['title'];
            $data['content'][$value['language']] = $value['content'];
        }

        return $data;
    }

    public function getHomePageSeoTextByLanguage(string $language)
    {
        /*
         * get() hands back a collection, which is never null, so the guard
         * below always passed and the row for this particular language was
         * read whether or not it existed. Filling the SEO text in for one
         * language and not the other took the other language's front page
         * down with a 500.
         */
        $seoText = SeoText::where('page_type', config('constants.HOMEPAGE_TYPE'))
            ->where('language', $language)
            ->first();

        if ($seoText) {
            return [
                'title' => $seoText->title,
                'content' => $seoText->content,
            ];
        }

        return null;
    }

    public function getNewProducts(): Collection
    {
        return Product::with(['productType'])->whereJsonContains('special_offers', ProductSpecialOfferOptionsDataClass::NEW)->limit(6)->get();
    }

    public function getProductsCustomFieldOptionsName(): ?string
    {
        $wallpapersProductType = ProductType::where('slug', config('domain.wallpaper_product_type_slug'))->first();

        $config = $this->getHomePageConfig();

        if ($wallpapersProductType && $config) {
            $field = $wallpapersProductType->fields->where('id', $config->product_field_id)->first();

            return mb_strtolower($field->pivot->filter_name);
        }

        return null;
    }

    public function getProductsByCustomFieldOptions(): array
    {
        // return Cache::remember('products-by-custom-field-options', 600, function () {
        $options = $this->getHomePageProductFieldOptions();
        $fieldId = $this->getHomePageConfig()?->product_field_id ?? null;
        $data = [];

        if ($fieldId && count($options)) {
            $productType = ProductType::select(['id'])->where('slug', config('domain.wallpaper_product_type_slug'))->first();

            foreach ($options as $option) {
                $firstProduct = Product::where('product_type_id', $productType->id)
                    ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS UNSIGNED) = CAST(? AS UNSIGNED)')
                    ->addBinding('$."'.$fieldId.'"')
                    ->addBinding((string) $option->product_field_option_id)
                    ->first();

                $productsCount = Product::where('product_type_id', $productType->id)
                    ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS UNSIGNED) = CAST(? AS UNSIGNED)')
                    ->addBinding('$."'.$fieldId.'"')
                    ->addBinding((string) $option->product_field_option_id)
                    ->count();

                $data[] = ['product' => $firstProduct, 'count' => $productsCount, 'option' => $option->option];
            }
        }

        return $data;
        // });
    }

    public function getInstagramFeed(): ?array
    {
        return app(InstagramFeedService::class)->getFeed();
    }
}
