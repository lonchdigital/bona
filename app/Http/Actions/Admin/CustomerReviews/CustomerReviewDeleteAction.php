<?php

namespace App\Http\Actions\Admin\CustomerReviews;

use App\Http\Actions\Admin\BaseAction;
use App\Models\CustomerReview;
use App\Services\CustomerReview\CustomerReviewService;
use Illuminate\Http\Request;

class CustomerReviewDeleteAction extends BaseAction
{
    public function __invoke(CustomerReview $customerReview, Request $request, CustomerReviewService $service)
    {
        $result = $service->delete($customerReview);

        return $this->handleActionResult(route('admin.customer-review.list.page'), $request, $result);
    }
}
