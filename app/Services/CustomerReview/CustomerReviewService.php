<?php

namespace App\Services\CustomerReview;

use App\DataClasses\ProductReviewStatusesDataClass;
use App\Models\CustomerReview;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\CustomerReview\DTO\SubmitCustomerReviewDTO;
use App\Services\HomePage\HomePageService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CustomerReviewService extends BaseService
{
    public function submit(SubmitCustomerReviewDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {
            CustomerReview::create([
                'source' => CustomerReview::SOURCE_WEBSITE,
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

    public function create(array $data): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($data) {
            CustomerReview::create($this->adminData($data));
            HomePageService::forgetStorefrontCache();

            return ServiceActionResult::make(true, trans('admin.customer_review_created'));
        });
    }

    public function update(CustomerReview $review, array $data): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review, $data) {
            $review->update($this->adminData($data, $review));
            HomePageService::forgetStorefrontCache();

            return ServiceActionResult::make(true, trans('admin.customer_review_updated'));
        });
    }

    public function approve(CustomerReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->update([
                'status_id' => ProductReviewStatusesDataClass::STATUS_APPROVED,
                'published_at' => $review->published_at ?: now(),
            ]);
            HomePageService::forgetStorefrontCache();

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
            HomePageService::forgetStorefrontCache();

            return ServiceActionResult::make(true, trans('admin.customer_review_rejected'));
        });
    }

    public function delete(CustomerReview $review): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($review) {
            $review->delete();
            HomePageService::forgetStorefrontCache();

            return ServiceActionResult::make(true, trans('admin.customer_review_deleted'));
        });
    }

    private function adminData(array $data, ?CustomerReview $review = null): array
    {
        $statusId = (int) $data['status_id'];

        return [
            'source' => $data['source'],
            'source_url' => filled($data['source_url'] ?? null) ? trim($data['source_url']) : null,
            'author_avatar_url' => filled($data['author_avatar_url'] ?? null) ? trim($data['author_avatar_url']) : null,
            'author_name' => trim($data['author_name']),
            'author_name_translations' => $this->translations($data['author_name_translations'] ?? []),
            'phone' => filled($data['phone'] ?? null) ? trim($data['phone']) : null,
            'email' => filled($data['email'] ?? null) ? trim($data['email']) : null,
            'rating' => (int) $data['rating'],
            'review' => trim($data['review']),
            'review_translations' => $this->translations($data['review_translations'] ?? []),
            'status_id' => $statusId,
            'published_at' => $statusId === ProductReviewStatusesDataClass::STATUS_APPROVED
                ? ($review?->published_at ?: now())
                : null,
            'reviewed_at' => ($data['reviewed_at'] ?? null) ?: null,
            'source_published_label' => filled($data['source_published_label'] ?? null)
                ? trim($data['source_published_label'])
                : null,
            'locale' => ($data['locale'] ?? null) ?: null,
        ];
    }

    private function translations(array $translations): ?array
    {
        $translations = collect($translations)
            ->map(fn (mixed $value) => trim((string) $value))
            ->filter()
            ->all();

        return $translations ?: null;
    }
}
