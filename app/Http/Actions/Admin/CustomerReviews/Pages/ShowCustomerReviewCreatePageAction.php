<?php

namespace App\Http\Actions\Admin\CustomerReviews\Pages;

class ShowCustomerReviewCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.customer-reviews.edit');
    }
}
