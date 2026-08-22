<?php

namespace App\Http\Actions\Store\ProductReview;

use App\Http\Requests\Store\ProductReview\SubmitProductReviewRequest;
use App\Services\ProductReview\ProductReviewService;
use Illuminate\Http\RedirectResponse;

class SubmitProductReviewAction
{
    public function __invoke(
        SubmitProductReviewRequest $request,
        ProductReviewService $productReviewService,
    ): RedirectResponse {
        $result = $productReviewService->submit($request->toDTO());

        return back()
            ->with($result->isSuccess() ? 'review_success' : 'review_error', $result->getMessage())
            ->withFragment('product-reviews');
    }
}
