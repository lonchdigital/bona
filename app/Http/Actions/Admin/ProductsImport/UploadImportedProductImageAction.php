<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\ProductsImport\UploadProductImportImageRequest;
use App\Models\ImportedProduct;
use App\Services\Product\ProductImportService;

class UploadImportedProductImageAction extends BaseAction
{
    public function __invoke(ImportedProduct $importedProduct, UploadProductImportImageRequest $request, ProductImportService $productImportService)
    {
        $result = $productImportService->uploadProductImportImage($importedProduct, $request->toDTO());

        return $this->handleActionResult('', $request, $result);
    }
}
