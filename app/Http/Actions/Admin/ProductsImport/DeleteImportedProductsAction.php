<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductType;
use App\Services\Product\ProductImportService;
use Illuminate\Http\Request;

class DeleteImportedProductsAction extends BaseAction
{
    public function __invoke(ProductType $productType, Request $request, ProductImportService $productImportService)
    {
        $result = $productImportService->deleteImportedProducts($productType, true);

        return $this->handleActionResult(route('admin.products-import.page', ['productType' => $productType->id]), $request, $result);
    }
}
