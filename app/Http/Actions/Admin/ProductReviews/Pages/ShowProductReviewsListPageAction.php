<?php

namespace App\Http\Actions\Admin\ProductReviews\Pages;

use App\Services\ProductReview\ProductReviewService;
use Illuminate\Http\Request;

class ShowProductReviewsListPageAction
{
    public function __invoke(Request $request, ProductReviewService $service)
    {
        $statusId = $request->integer('status') ?: null;

        return view('pages.admin.product-reviews.list', [
            'reviewsPaginated' => $service->getReviewsPaginated($statusId),
            'selectedStatusId' => $statusId,
        ]);
    }
}
