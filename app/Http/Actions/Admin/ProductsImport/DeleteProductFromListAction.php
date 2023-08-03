<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ImportedProduct;
use App\Services\Product\ProductImportService;
use Illuminate\Http\Request;

class DeleteProductFromListAction extends BaseAction
{
    public function __invoke(
        ImportedProduct $importedProduct,
        Request $request,
        ProductImportService $productImportService,
    )
    {
        $productType = $importedProduct->productType->id;

        $result = $productImportService->deleteImportedProduct($importedProduct);

        return $this->handleActionResult(route('admin.products-import.list', ['productType' => $productType]), $request, $result);
    }
}
