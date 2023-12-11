<?php

namespace App\Http\Actions\Admin\ProductsImport\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductsImport\ProductImportFilterRequest;
use App\Models\ProductType;
use App\Services\Brand\BrandService;
use App\Services\Color\ColorService;
use App\Services\Country\CountryService;
use App\Services\Product\ProductImportService;

class ShowImportedProductsListPageAction extends BaseAction
{
    public function __invoke(
        ProductType $productType,
        ProductImportFilterRequest $request,
        ProductImportService $productImportService,
        BrandService         $brandService,
        ColorService         $colorService,
        CountryService       $countryService,
    )
    {
        if (!$productImportService->importedProductsExists($productType)) {
            return redirect()->route('admin.products-import.page', [
                'productType' => $productType->id,
            ]);
        }

        $dto = $request->toDTO();

        return view('pages.admin.products-import.imported-products-list', [
            'brands' => $brandService->getBrands(),
            'colors' => $colorService->getColors(),
            'countries' => $countryService->getCountries(),
            'searchData' => $dto,
            'productType' => $productType,
            'importedProductsPaginated' => $productImportService->getImportedProductsByProductTypePaginated($productType, $dto),
        ]);
    }
}
