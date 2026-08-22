<?php

namespace App\Http\Actions\Admin\ProductReviews;

use App\Http\Actions\Admin\BaseAction;
use App\Models\ProductReview;
use App\Services\ProductReview\ProductReviewService;
use Illuminate\Http\Request;

class ProductReviewRejectAction extends BaseAction
{
    public function __invoke(ProductReview $productReview, Request $request, ProductReviewService $service)
    {
        $result = $service->reject($productReview);

        return $this->handleActionResult(route('admin.product-review.list.page'), $request, $result);
    }
}
