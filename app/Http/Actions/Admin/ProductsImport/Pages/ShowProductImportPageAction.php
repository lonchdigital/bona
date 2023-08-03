<?php

namespace App\Http\Actions\Admin\ProductsImport\Pages;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductImportService;

class ShowProductImportPageAction extends BaseAction
{
    public function __invoke(ProductType $productType, ProductImportService $productImportService)
    {
        if ($productImportService->importedProductsExists($productType)) {
            return redirect()->route('admin.products-import.list', ['productType' => $productType->id]);
        }

        return view('pages.admin.products-import.upload', [
            'productType' => $productType,
        ]);
    }
}
