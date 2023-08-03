<?php

namespace App\Http\Actions\Admin\ProductsImport;

use App\Models\ProductType;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\Product\ProductImportService;


class SaveImportedProductAction extends BaseAction
{
    public function __invoke(ProductType $productType, Request $request,  ProductImportService $productImportService)
    {
        $result = $productImportService->saveImportedProducts($productType, $this->getAuthUser());

        if ($result->isSuccess()) {
            return $this->handleActionResult(route('admin.products-import.page', ['productType' => $productType->id]), $request, $result);
        } else {
            return $this->handleActionResult(route('admin.products-import.list', ['productType' => $productType->id]), $request, $result);
        }

    }
}
