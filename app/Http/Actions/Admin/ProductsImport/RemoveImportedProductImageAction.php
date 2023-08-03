<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Models\ImportedProduct;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductImportService;
use App\Http\Requests\Admin\ProductsImport\RemoveProductImportImageRequest;

class RemoveImportedProductImageAction extends BaseAction
{
    public function __invoke(ImportedProduct $importedProduct, RemoveProductImportImageRequest $request, ProductImportService $productImportService)
    {
        $result = $productImportService->removeProductImportImage($importedProduct, $request->toDTO());

        return $this->handleActionResult('', $request, $result);
    }
}
