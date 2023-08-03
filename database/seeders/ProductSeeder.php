<?php

namespace Database\Seeders;

use App\DataClasses\ProductFieldTypeOptionsDataClass;
use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use App\DataClasses\ProductStatusDataClass;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Color;
use App\Models\Country;
use App\Models\Currency;
use App\Models\ImportedProduct;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Role;
use App\Models\User;
use App\Services\ProductCategory\CategoryService;
use App\Services\Product\DTO\EditProductDTO;
use App\Services\Product\ProductService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Laravel\Telescope\Telescope;

class ProductSeeder extends Seeder
{
    use WithFaker, WithoutModelEvents;
    /**
     * Run the database seeds.
     */

    public function __construct()
    {
        $this->setUpFaker();
    }

    public function run(ProductService $productService, CategoryService $categoryService): void
    {
        Telescope::stopRecording();

        $creator = User::where('role_id', Role::ADMIN_ROLE_ID)->first();

        if (!$creator) {
            throw new \Exception('User with admin roles is not exists!');
        }

        $productsCount = 10;

        $currencyUSDId = 2;

        $basePath = base_path() . '/resources/seed/products';

        $productType = ProductType::first();

        for ($i = 0; $i < $productsCount; $i++) {
            $fakeCode = $this->faker->randomNumber(3);
            $brand = $this->getRandomBrand();

            $isActive = true;
            $name =  [
                'uk' => 'Шпалери ' . $brand->name . ' тип ' . $fakeCode,
                'ru' => 'Обои ' . $brand->name . ' тип ' . $fakeCode,
            ];
            $sku = $this->faker->numerify(mb_strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()) . '00#########');
            $slug = Str::slug($name['uk'] . '-' . $sku);
            $metaData = [
                'uk' => 'Купити шпалери',
                'ru' => 'Купить обои',
            ];

            $specialOffers = ProductSpecialOfferOptionsDataClass::get()->pluck('id')->random(rand(0, rand(0, rand(0, 2))))->toArray();

            if (!count($specialOffers)) {
                $specialOffers = null;
            }

            $priceInUSD = $this->faker->randomFloat(2, 5, 40);
            $salePrice = $this->faker->boolean(30) ? $priceInUSD * 1.30 : null;

            $mainImage = UploadedFile::fake()->createWithContent('main-image.jpg', file_get_contents($basePath . '/' . 'main-image.jpg'));
            $patternImage = UploadedFile::fake()->createWithContent('main-image.jpg', file_get_contents($basePath . '/' . 'pattern-image.jpg'));

            $countryId = $this->getRandomCountry()->id;
            $brandId = $brand->id;
            $collectionId = $this->getRandomCollection($brand)->id;
            $categories = Category::get()->pluck('id')->toArray();
            $customFields = $this->getRandomCustomFields($productType);

            $length = rand(10, 50);
            $width = collect([0.5, 0.52, 0.75, 0.53, 0.7, 0.32, 1.06])->random();

            $dto = new EditProductDTO(
                $isActive,
                $name,
                $slug,
                $metaData,
                $metaData,
                $metaData,
                null,
                ProductStatusDataClass::PRODUCT_STATUS_STOCK,
                $specialOffers,
                $sku,
                $salePrice,
                $priceInUSD,
                $priceInUSD * 0.50,
                $currencyUSDId,
                $mainImage,
                false,
                $patternImage,
                false,
                null,
                false,
                null,
                false,
                null,
                false,
                null,
                false,
                null,
                false,
                $countryId,
                $brandId,
                $collectionId,
                $categories,
                $this->getRandomColorId(1)->first(),
                $this->getRandomColorId(rand(1, 3))->toArray(),
                $customFields,
                $length,
                $width,
                null,
            );

            $productService->createProduct($creator, $productType, $dto);

            $productId = Product::latest()->first()->id;

            for($child = 0; $child < rand(1, 3); $child++) {

                $sku = $this->faker->numerify(mb_strtoupper($this->faker->randomLetter() . $this->faker->randomLetter()) . '00#########');
                $slug = Str::slug($name['uk'] . '-' . $sku);

                $childDTO = new EditProductDTO(
                    $isActive,
                    $name,
                    $slug,
                    $metaData,
                    $metaData,
                    $metaData,
                    $productId,
                    ProductStatusDataClass::PRODUCT_STATUS_STOCK,
                    $specialOffers,
                    $sku,
                    $salePrice,
                    $priceInUSD,
                    $priceInUSD * 0.50,
                    $currencyUSDId,
                    $mainImage,
                    false,
                    $patternImage,
                    false,
                    null,
                    false,
                    null,
                    false,
                    null,
                    false,
                    null,
                    false,
                    null,
                    false,
                    $countryId,
                    $brandId,
                    $collectionId,
                    $categories,
                    $this->getRandomColorId(1)->first(),
                    $this->getRandomColorId(rand(1, 3))->toArray(),
                    $customFields,
                    $length,
                    $width,
                    null,
                );

                $productService->createProduct($creator, $productType, $childDTO);
            }
        }

        $categoryService->updateCountOfProductsByCategory(Category::get());

        Telescope::startRecording();
    }

    private function getRandomBrand()
    {
        return Brand::get()->random();
    }

    private function getRandomCountry()
    {
        return Country::get()->random();
    }

    private function getRandomCollection(Brand $brand)
    {
        return $brand->collections->random();
    }

    private function getRandomColorId(int $count)
    {
        return Color::get()->random($count)->pluck('id');
    }

    private function getRandomCustomFields(ProductType $productType): array
    {
        $customFields = [];

        foreach ($productType->fields as $customProductField) {
            $fieldId = $customProductField->id;

            $value = null;

            switch ($customProductField->field_type_id) {
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_STRING:
                    $value = $this->faker->realTextBetween(10, 20);
                    break;
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_SIZE:
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_NUMBER:
                    $value = $this->faker->randomNumber(1, 999);
                    break;
                case ProductFieldTypeOptionsDataClass::FIELD_TYPE_OPTION:
                    if ($customProductField->is_multiselectable) {
                        $value = $customProductField->options->random(rand(1, 5))->pluck('id');
                    } else {
                        $value = $customProductField->options->random()->id;
                    }
                    break;
            }

            $customFields[$fieldId] = [
                'field_id' => $fieldId,
                'value' => $value
            ];
        }

        return $customFields;
    }
}
