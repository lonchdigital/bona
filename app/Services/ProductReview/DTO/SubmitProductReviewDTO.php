<?php

namespace App\Services\ProductReview\DTO;

use App\Services\Base\DTO\BaseDTO;

class SubmitProductReviewDTO implements BaseDTO
{
    public function __construct(
        public readonly int $productId,
        public readonly string $authorName,
        public readonly ?string $authorEmail,
        public readonly int $rating,
        public readonly string $review,
        public readonly ?string $ipAddress,
    ) {}
}
