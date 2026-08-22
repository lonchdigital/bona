<?php

namespace App\Http\Requests\Store\ProductReview;

use App\Http\Requests\BaseRequest;
use App\Services\ProductReview\DTO\SubmitProductReviewDTO;

class SubmitProductReviewRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function baseRules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'author_name' => ['required', 'string', 'min:2', 'max:120'],
            'author_email' => ['required', 'email', 'max:191'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'string', 'min:20', 'max:2000'],
            // Honeypot: a field a person never sees and never fills in.
            'website' => ['nullable', 'size:0'],
        ];
    }

    public function rules(): array
    {
        return $this->baseRules();
    }

    public function attributes(): array
    {
        return [
            'author_name' => mb_strtolower(trans('base.name')),
            'author_email' => mb_strtolower(trans('base.email')),
            'rating' => mb_strtolower(trans('base.product_review_rating')),
            'review' => mb_strtolower(trans('base.product_review_text')),
        ];
    }

    public function toDTO(): SubmitProductReviewDTO
    {
        return new SubmitProductReviewDTO(
            productId: (int) $this->input('product_id'),
            authorName: trim((string) $this->input('author_name')),
            authorEmail: $this->input('author_email'),
            rating: (int) $this->input('rating'),
            review: trim((string) $this->input('review')),
            ipAddress: $this->ip(),
        );
    }
}
