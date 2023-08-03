<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductImportService;

class DeleteImportedProductsAction extends BaseAction
{
    public function __invoke(ProductType $productType, Request $request, ProductImportService $productImportService)
    {
        $result = $productImportService->deleteImportedProducts($productType, true);

        return $this->handleActionResult(route('admin.products-import.page', ['productType' => $productType->id]), $request, $result);
    }
}
