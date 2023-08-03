<?php

namespace App\Services\Country;

use App\Models\Country;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\User;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\Country\DTO\EditCountryDTO;
use DOMDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CountryService extends BaseService
{
    public const COUNTRY_IMAGES_FOLDER = 'country-images';

    public function getCountries(): Collection
    {
        return Country::get();
    }

    public function getCountriesPaginated(): LengthAwarePaginator
    {
        return Country::with('creator')->paginate(config('domain.items_per_page'));
    }

    public function getAvailableCountriesByProductType(ProductType $productType)
    {
        //TODO: implement with cache
        return Country::get();
    }

    public function createCountry(User $creator, EditCountryDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request, $creator) {
            $path = self::COUNTRY_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.svg';

            $this->storeCountryImage($request->image, $path);

            Country::create([
                'creator_id' => $creator->id,
                'name' => $request->name,
                'code' => $request->code,
                'image_path' => $path,
            ]);

            return ServiceActionResult::make(true, trans('admin.country_create_success'));
        });
    }

    public function editCountry(Country $country, EditCountryDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($country, $request) {
            $oldImagePath = $country->image_path;
            $dataToUpdate = [
                'name' => $request->name,
                'code' => $request->code,
            ];

            if ($request->image) {
                $newImagePath = self::COUNTRY_IMAGES_FOLDER . '/'  . sha1(time()) . '_' . Str::random(10) . '.svg';

                $this->storeCountryImage($request->image, $newImagePath);

                $dataToUpdate['image_path'] = $newImagePath;
            }

            $country->update($dataToUpdate);

            if ($request->image && $oldImagePath) {
                $this->deleteCountryImage($oldImagePath);
            }

            return ServiceActionResult::make(true, trans('admin.country_edit_success'));

        });
    }

    public function deleteCountry(Country $country): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($country) {
            if (Product::where('country_id', $country->id)->exists()) {
                return ServiceActionResult::make(false, trans('admin.country_in_use'));
            }

            $country->delete();

            $this->deleteCountryImage($country->image_path);

            return ServiceActionResult::make(true, trans('admin.country_delete_success'));
        });
    }

    private function handleSvgImage(UploadedFile $imageToStore, int $outputWidth, int $outputHeight): string
    {
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->load($imageToStore->path());
        $svg = $dom->documentElement;
        if ( ! $svg->hasAttribute('viewBox') ) {
            $pattern = '/^(\d*\.\d+|\d+)(px)?$/';

            $interpretable =  preg_match( $pattern, $svg->getAttribute('width'), $width ) &&
                preg_match( $pattern, $svg->getAttribute('height'), $height );

            if ( $interpretable ) {
                $view_box = implode(' ', [0, 0, $width[0], $height[0]]);
                $svg->setAttribute('viewBox', $view_box);
            } else { // this gets sticky
                throw new \Exception("viewBox is dependent on environment");
            }
        }

        $svg->setAttribute('width', $outputWidth);
        $svg->setAttribute('height', $outputHeight);

        return $dom->saveXML($svg);

    }

    private function storeCountryImage(UploadedFile $imageToStore, string $path): void
    {
        $image = $this->handleSvgImage($imageToStore, 28, 20);

        Storage::disk(config('app.images_disk_default'))->put($path, $image);
    }

    private function deleteCountryImage(string $imagePath): void
    {
        if (Storage::disk(config('app.images_disk_default'))->exists($imagePath)) {
            Storage::disk(config('app.images_disk_default'))->delete($imagePath);
        }
    }


}
