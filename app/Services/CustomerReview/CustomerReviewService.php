<?php

namespace App\Services\CustomerReview;

use App\DataClasses\ProductReviewStatusesDataClass;
use App\Models\CustomerReview;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\CustomerReview\DTO\SubmitCustomerReviewDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerReviewService extends BaseService
{
    public function submit(SubmitCustomerReviewDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {
            CustomerReview::create([
                'author_name' => $request->authorName,
                'phone' => $request->phone,
                'email' => $request->email,
                'rating' => $request->rating,
                'review' => $request->review,
                'status_id' => ProductReviewStatusesDataClass::STATUS_PENDING,
                'locale' => $request->locale,
                'ip_address' => $request->ipAddress,
            ]);

            return ServiceActionResult::make(true, trans('base.customer_review_sent'));
        });
    }

    public function getReviewsPaginated(?int $statusId = null): LengthAwarePaginator
    {
        return CustomerReview::query()
            ->when($statusId, fn ($query) => $query->where('status_id', $statusId))
            ->orderByDesc('id')
            ->paginate(config('domain.items_per_page'));
    }

    public function getApprovedReviews(): Collection
    {
        return CustomerReview::approved()
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->get();
    }

    public function approve(CustomerReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->update([
                'status_id' => ProductReviewStatusesDataClass::STATUS_APPROVED,
                'published_at' => $review->published_at ?: now(),
            ]);

            return ServiceActionResult::make(true, trans('admin.customer_review_approved'));
        });
    }

    public function reject(CustomerReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->update([
                'status_id' => ProductReviewStatusesDataClass::STATUS_REJECTED,
                'published_at' => null,
            ]);

            return ServiceActionResult::make(true, trans('admin.customer_review_rejected'));
        });
    }

    public function delete(CustomerReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->delete();

            return ServiceActionResult::make(true, trans('admin.customer_review_deleted'));
        });
    }
}
