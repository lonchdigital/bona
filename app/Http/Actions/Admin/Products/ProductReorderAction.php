<?php

namespace App\Http\Actions\Admin\Products;

use App\Http\Requests\Admin\Product\ProductReorderRequest;
use App\Models\ProductType;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductReorderAction
{
    public function __invoke(
        ProductType $productType,
        ProductReorderRequest $request,
        ProductService $productService,
    ): JsonResponse {
        $result = $productService->reorderProducts($productType, $request->validated('product_ids'));

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
        ], $result->isSuccess() ? 200 : 422);
    }
}
