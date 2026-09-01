<?php

namespace App\Services\CustomerReview\DTO;

use App\Services\Base\DTO\BaseDTO;

class SubmitCustomerReviewDTO implements BaseDTO
{
    public function __construct(
        public readonly string $authorName,
        public readonly string $phone,
        public readonly ?string $email,
        public readonly int $rating,
        public readonly string $review,
        public readonly string $locale,
        public readonly ?string $ipAddress,
    ) {}
}
