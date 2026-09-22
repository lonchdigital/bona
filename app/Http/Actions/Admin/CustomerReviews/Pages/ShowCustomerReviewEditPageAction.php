<?php

namespace App\Http\Actions\Admin\CustomerReviews\Pages;

use App\Models\CustomerReview;

class ShowCustomerReviewEditPageAction
{
    public function __invoke(CustomerReview $customerReview)
    {
        return view('pages.admin.customer-reviews.edit', [
            'review' => $customerReview,
        ]);
    }
}
