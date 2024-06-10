<?php

namespace App\Services\HomePage;

use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\Models\HomePageBestSalesProducts;
use App\Models\HomePageNewProducts;
use App\Models\HomePageBrands;
use App\Models\HomePageConfig;
use App\Models\HomePageProductOptions;
use App\Models\Faqs;
use App\Models\HomePageSlides;
use App\Models\HomePageTestimonials;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\SeoText;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\HomePage\DTO\HomePageEditDTO;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class HomePageService extends BaseService
{
    const HOME_PAGE_IMAGES_FOLDER = 'home-page-images';

    public function editHomePage(HomePageEditDTO $request): ServiceActionResult
    {
//        dd( $request->testimonials );

        return $this->coverWithDBTransaction(function () use($request) {
            $homePageConfig = $this->getHomePageConfig();
            $dataToUpdate = [
                'meta_title' => $request->metaTitle,
                'meta_description' => $request->metaDescription,
                'meta_keywords' => $request->metaKeyWords,
                'meta_tags' => $request->metaTags,
            ];

            if ($homePageConfig) {
                $homePageConfig->update($dataToUpdate);
            } else {
                HomePageConfig::create($dataToUpdate);
            }

            $this->syncSlides($request->slides);
            $this->syncTestimonials($request->testimonials);
            $this->syncFaqs(config('constants.HOMEPAGE_TYPE'), $request->faqs);
            SeoText::updateSeoText(config('constants.HOMEPAGE_TYPE'), $request->seoTitle, $request->seoText);

            $this->syncNewProducts($request->selectedProductsId);
            $this->syncBestSalesProducts($request->selectedBestSalesProductsId);

             return ServiceActionResult::make(true, trans('admin.home_page_edit_success'));
        });
    }



    private function syncNewProducts(?array $productsId): void
    {
        HomePageNewProducts::query()->delete();

        if (!empty($productsId) && $productsId[0] != '' ) {
            foreach ($productsId as $productId) {
                HomePageNewProducts::create(['product_id' => $productId]);
            }
        }
    }
    private function syncBestSalesProducts(?array $productsId): void
    {
        HomePageBestSalesProducts::query()->delete();

        if (!empty($productsId) && $productsId[0] != '' ) {
            foreach ($productsId as $productId) {
                HomePageBestSalesProducts::create(['product_id' => $productId]);
            }
        }
    }
    private function syncBrands(array $brandsId): void
    {
        HomePageBrands::query()->delete();

        foreach ($brandsId as $brandId) {
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
                    'title' => $slide['title'],
                    'description' => $slide['description'],
                    'button_text' => $slide['button_text'],
                    'button_url' => $slide['button_url']
                ];

                if (isset($slide['image'])) {
                    $slideImagePath = self::HOME_PAGE_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10);

                    $this->storeImage($slideImagePath, $slide['image'], 'webp');
                    $this->storeImage($slideImagePath, $slide['image'], 'jpg');

                    $dataToUpdate['slide_image_path'] = $slideImagePath . '.webp';
                }

                if (isset($slide['id']) && $slide['id']) {
                    $existingSlide = $existingSlides->where('id', $slide['id'])->first();
                    if (!$existingSlide) {
                        throw new \Exception('Incorrect slide id: ' . $slide['id']);
                    }

                    if (isset($slide['image'])) {
                        $imagesToDelete[] = $existingSlide->slide_image_path;
                    }

                    $existingSlide->update($dataToUpdate);
                } else {
                    HomePageSlides::create($dataToUpdate);
                }
            }
        }

        $existingSlidesInRequest = $slides ? array_filter(array_column($slides, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $slidesToDelete = $existingSlides->whereNotIn('id', $existingSlidesInRequest);

        foreach ($slidesToDelete as $slideToDelete) {
            $imagesToDelete[] = $slideToDelete->slide_image_path;
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
                    $testimonialImagePath = self::HOME_PAGE_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10);

                    $this->storeImage($testimonialImagePath, $testimonial['image'], 'webp');
                    $this->storeImage($testimonialImagePath, $testimonial['image'], 'jpg');

                    $dataToUpdate['testimonial_image_path'] = $testimonialImagePath . '.webp';
                }

                if (isset($testimonial['id']) && $testimonial['id']) {
                    $existingTestimonial = $existingTestimonials->where('id', $testimonial['id'])->first();
                    if (!$existingTestimonial) {
                        throw new \Exception('Incorrect testimonial id: ' . $testimonial['id']);
                    }

                    if (isset($testimonial['image'])) {
                        $imagesToDelete[] = $existingTestimonial->testimonial_image_path;
                    }

                    $existingTestimonial->update($dataToUpdate);
                } else {
//                    dd('I am here?');
                    HomePageTestimonials::create($dataToUpdate);
                }
            }
        }

        $existingTestimonialsInRequest = $testimonials ? array_filter(array_column($testimonials, 'id'), function ($item) {
            return $item !== null;
        }): [];

        $testimonialsToDelete = $existingTestimonials->whereNotIn('id', $existingTestimonialsInRequest);

        foreach ($testimonialsToDelete as $testimonialToDelete) {
            $imagesToDelete[] = $testimonialToDelete->testimonial_image_path;
            $testimonialToDelete->delete();
        }

        foreach ($imagesToDelete as $imageToDelete) {
            $this->deleteImage($imageToDelete);
        }

    }

    public function getHomePageConfig(): ?HomePageConfig {
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

    public function getHomePageProductTypes(): Collection
    {
        return ProductType::whereIn('id', [1, 2, 3, 4, 13, 17, 18])->get(); // 23, 5
    }

    public function getSpecificProductTypes(): Collection
    {
        return ProductType::whereNotNull('code_name')->with(['categories'])->get();
    }

    public function getHomePageBestSalesProducts(): Collection
    {
        return HomePageBestSalesProducts::with(['product'])->get();
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
        $seoTextData = SeoText::where('page_type', config('constants.HOMEPAGE_TYPE'))->get();
        $data = [];

        if ($seoTextData) {
            $data['title'] = $seoTextData->where('language', $language)->first()->title;
            $data['content'] = $seoTextData->where('language', $language)->first()->content;
            return $data;
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
                        ->addBinding('$."' . $fieldId . '"')
                        ->addBinding((string)$option->product_field_option_id)
                        ->first();

                    $productsCount = Product::where('product_type_id', $productType->id)
                        ->whereRaw('CAST(JSON_EXTRACT(custom_fields, ?) AS UNSIGNED) = CAST(? AS UNSIGNED)')
                        ->addBinding('$."' . $fieldId . '"')
                        ->addBinding((string)$option->product_field_option_id)
                        ->count();

                    $data[] = ['product' => $firstProduct, 'count' => $productsCount, 'option' => $option->option];
                }
            }

            return $data;
        //});
    }
}
