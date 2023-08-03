<?php

namespace App\Services\HomePage;

use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\Models\HomePageBrands;
use App\Models\HomePageConfig;
use App\Models\HomePageProductOptions;
use App\Models\HomePageSlides;
use App\Models\Product;
use App\Models\ProductType;
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
        return $this->coverWithDBTransaction(function () use($request) {
            $imagesToDelete = [];



            $existingConfig = HomePageConfig::first();

            $dataToUpdate = [
                'slider_title' => $request->sliderTitle,
                'collection_id' => $request->collectionId,
                'product_field_id' => $request->selectedFieldId,
            ];

            if ($request->sliderLogo) {
                $sliderLogoImagePath = self::HOME_PAGE_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10) . '.jpg';

                $dataToUpdate['slider_logo_image_path'] = $sliderLogoImagePath;

                $this->storeHomePageImage($sliderLogoImagePath, $request->sliderLogo);
            }

            if ($existingConfig) {
                if ($request->sliderLogo) {
                    $imagesToDelete[] = $existingConfig->slider_logo_image_path;
                }

                $existingConfig->update($dataToUpdate);
            } else {
                HomePageConfig::create($dataToUpdate);
            }

            $this->syncSlides($request->slides);

            $this->syncBrands($request->selectedBrandsId);
            $this->syncOptions($request->selectedOptionsId);

            foreach ($imagesToDelete as $imageToDelete) {
                $this->deleteHomePageImage($imageToDelete);
            }

             return ServiceActionResult::make(true, trans('admin.home_page_edit_success'));
        });
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
                    'description' => $slide['description']
                ];

                if (isset($slide['image'])) {
                    $slideImagePath = self::HOME_PAGE_IMAGES_FOLDER . '/' . sha1(time()) . '_' . Str::random(10) . '.jpg';
                    $this->storeHomePageImage($slideImagePath, $slide['image']);
                    $dataToUpdate['slide_image_path'] = $slideImagePath;
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
            $this->deleteHomePageImage($imageToDelete);
        }

    }

    public function getHomePageConfig(): ?HomePageConfig {
        return HomePageConfig::first();
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

    private function storeHomePageImage(string $path, UploadedFile $image): void
    {
        $image = Image::make($image)->encode('jpg', 100);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }

    private function deleteHomePageImage(string $path): void
    {
        if (Storage::disk(config('app.images_disk_default'))->exists($path)) {
            Storage::disk(config('app.images_disk_default'))->delete($path);
        }
    }
}
