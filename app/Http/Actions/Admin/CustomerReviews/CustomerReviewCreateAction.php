<?php

namespace App\Http\Actions\Admin\CustomerReviews;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\CustomerReviews\SaveCustomerReviewRequest;
use App\Services\CustomerReview\CustomerReviewService;

class CustomerReviewCreateAction extends BaseAction
{
    public function __invoke(SaveCustomerReviewRequest $request, CustomerReviewService $service)
    {
        $result = $service->create($request->validated());

        return $this->handleActionResult(route('admin.customer-review.list.page'), $request, $result);
    }
}
