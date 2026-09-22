<?php

namespace App\Http\Actions\Admin\CustomerReviews;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\CustomerReviews\SaveCustomerReviewRequest;
use App\Models\CustomerReview;
use App\Services\CustomerReview\CustomerReviewService;

class CustomerReviewEditAction extends BaseAction
{
    public function __invoke(
        CustomerReview $customerReview,
        SaveCustomerReviewRequest $request,
        CustomerReviewService $service,
    ) {
        $result = $service->update($customerReview, $request->validated());

        return $this->handleActionResult(route('admin.customer-review.list.page'), $request, $result);
    }
}
