<?php

namespace App\Http\Actions\Admin\CustomerReviews\Pages;

use App\Services\CustomerReview\CustomerReviewService;
use Illuminate\Http\Request;

class ShowCustomerReviewsListPageAction
{
    public function __invoke(Request $request, CustomerReviewService $service)
    {
        $statusId = $request->integer('status') ?: null;

        return view('pages.admin.customer-reviews.list', [
            'reviewsPaginated' => $service->getReviewsPaginated($statusId),
            'selectedStatusId' => $statusId,
        ]);
    }
}
