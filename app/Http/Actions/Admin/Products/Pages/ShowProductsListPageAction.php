<?php

namespace App\Http\Actions\Admin\Products\Pages;

use App\Http\Requests\Admin\Product\ProductFilterRequest;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\Country\CountryService;
use App\Services\Product\ProductService;
use App\Services\ProductCategory\CategoryService;
use Illuminate\Support\Str;

class ShowProductsListPageAction
{
    public function __invoke(
        ProductFilterRequest $request,
        ProductType $productType,
        ProductService $service,
        BrandService $brandService,
        CountryService $countryService,
        CategoryService $categoryService,
    ) {
        $dto = $request->toDTO();
        $productType->loadMissing(['fields.options']);
        $styleField = $productType->fields->first(function ($field): bool {
            $names = collect($field->getTranslations('field_name'))
                ->map(fn ($name) => Str::lower((string) $name));

            return Str::contains(Str::lower((string) $field->slug), ['style', 'styl']) ||
                $names->contains(fn (string $name) => Str::contains($name, ['стиль', 'стил']));
        });

        if ($styleField) {
            $styleField->setRelation('options', $styleField->optionsWithProducts($productType)->values());
        }

        $productsPaginated = $service->getProductsByTypePaginatedAdmin($productType, $dto, $styleField?->id);

        return view('pages.admin.products.list', [
            'productType' => $productType,
            'productsPaginated' => $productsPaginated,
            'brands' => $brandService->getAvailableBrandsForAdminProductType($productType),
            'countries' => $countryService->getCountries(),
            'categories' => $categoryService->getProductCategories($productType),
            'styleField' => $styleField,
            'searchData' => $dto,
        ]);
    }
}
