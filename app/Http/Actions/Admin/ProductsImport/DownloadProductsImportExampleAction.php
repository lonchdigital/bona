<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Models\ProductType;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductImportDownloadExampleService;

class DownloadProductsImportExampleAction extends BaseAction
{
    public function __invoke(ProductType $productType, ProductImportDownloadExampleService $productImportDownloadExampleService)
    {
        return $productImportDownloadExampleService->downloadProductsImportExample($productType);
    }
}
