<?php

namespace App\Services\SerpAgent\DTO;

use App\Services\Base\DTO\BaseDTO;

class SerpAgentRetirementDTO implements BaseDTO
{
    public function __construct(
        public readonly string $deliveryId,
        public readonly string $slug,
        /** The one language version this delivery takes off the site. */
        public readonly string $locale,
        /** The address Serp Agent knows the article under, used to find it when the slug does not. */
        public readonly ?string $url,
        /** Null answers the old address with 410 instead of a redirect. */
        public readonly ?string $redirectTo,
        /** Check everything, change nothing. */
        public readonly bool $isTest,
    ) {}
}
