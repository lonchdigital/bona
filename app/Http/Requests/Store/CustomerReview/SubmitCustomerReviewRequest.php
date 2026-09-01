<?php

namespace App\Http\Requests\Store\CustomerReview;

use App\Http\Requests\BaseRequest;
use App\Services\CustomerReview\DTO\SubmitCustomerReviewDTO;

class SubmitCustomerReviewRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'min:2', 'max:60'],
            'last_name' => ['required', 'string', 'min:2', 'max:60'],
            'phone' => ['required', 'string', 'regex:/^\+38 \(0\d{2}\) \d{3} \d{2} \d{2}$/'],
            'email' => ['nullable', 'email', 'max:191'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'review' => ['required', 'string', 'min:20', 'max:2000'],
            'agree' => ['accepted'],
            // Honeypot: invisible to people and expected to stay empty.
            'website' => ['nullable', 'size:0'],
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => mb_strtolower(trans('base.name')),
            'last_name' => mb_strtolower(trans('base.last_name')),
            'phone' => mb_strtolower(trans('base.phone')),
            'email' => mb_strtolower(trans('base.email')),
            'rating' => mb_strtolower(trans('base.product_review_rating')),
            'review' => mb_strtolower(trans('base.product_review_text')),
            'agree' => mb_strtolower(trans('base.agree')),
        ];
    }

    public function toDTO(): SubmitCustomerReviewDTO
    {
        $email = trim((string) $this->input('email'));

        return new SubmitCustomerReviewDTO(
            authorName: trim($this->string('first_name').' '.$this->string('last_name')),
            phone: $this->string('phone')->toString(),
            email: $email !== '' ? mb_strtolower($email) : null,
            rating: (int) $this->input('rating'),
            review: trim((string) $this->input('review')),
            locale: app()->getLocale(),
            ipAddress: $this->ip(),
        );
    }
}
