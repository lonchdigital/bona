<?php

namespace App\Services\ProductReview;

use App\DataClasses\ProductReviewStatusesDataClass;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\ProductReview\DTO\SubmitProductReviewDTO;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ProductReviewService extends BaseService
{
    /**
     * A review arrives unpublished and waits for a person to look at it.
     * Moderation is there to catch spam, not to hide criticism: publishing only
     * the flattering ones is against Google's rules and reads as fake anyway.
     */
    public function submit(SubmitProductReviewDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {
            ProductReview::create([
                'product_id' => $request->productId,
                'author_name' => $request->authorName,
                'author_email' => $request->authorEmail,
                'rating' => $request->rating,
                'review' => $request->review,
                'status_id' => ProductReviewStatusesDataClass::STATUS_PENDING,
                'ip_address' => $request->ipAddress,
            ]);

            return ServiceActionResult::make(true, trans('base.product_review_sent'));
        });
    }

    public function getReviewsPaginated(?int $statusId = null, ?int $productId = null): LengthAwarePaginator
    {
        return ProductReview::with('product')
            ->when($statusId, fn ($query) => $query->where('status_id', $statusId))
            ->when($productId, fn ($query) => $query->where('product_id', $productId))
            ->orderByDesc('id')
            ->paginate(config('domain.items_per_page'));
    }

    public function getApprovedReviews(Product $product): Collection
    {
        return $product->approvedReviews()->get();
    }

    /**
     * The numbers behind AggregateRating. Null when nothing has been approved
     * yet: an empty or invented rating is exactly what earns a manual penalty.
     */
    public function getRatingSummary(Product $product): ?array
    {
        $reviews = $product->approvedReviews()->get();

        if ($reviews->isEmpty()) {
            return null;
        }

        return [
            'count' => $reviews->count(),
            'average' => round($reviews->avg('rating'), 1),
            'best' => 5,
            'worst' => 1,
        ];
    }

    public function approve(ProductReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->update([
                'status_id' => ProductReviewStatusesDataClass::STATUS_APPROVED,
                'published_at' => $review->published_at ?: now(),
            ]);

            return ServiceActionResult::make(true, trans('admin.product_review_approved'));
        });
    }

    public function reject(ProductReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->update([
                'status_id' => ProductReviewStatusesDataClass::STATUS_REJECTED,
                'published_at' => null,
            ]);

            return ServiceActionResult::make(true, trans('admin.product_review_rejected'));
        });
    }

    public function delete(ProductReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->delete();

            return ServiceActionResult::make(true, trans('admin.product_review_deleted'));
        });
    }
}
