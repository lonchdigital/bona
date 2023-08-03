<?php

namespace App\Services\VisitRequest\DTO;

use App\Services\Base\DTO\BaseDTO;

class VisitRequestCreateDTO implements BaseDTO
{
    public function __construct(
        public readonly int $requestTypeId,
        public readonly string $visitRequestName,
        public readonly string $visitRequestPhone,
        public readonly ?string $visitRequestEmail,
        public readonly ?string $visitRequestDateDay,
        public readonly ?string $visitRequestDateMonth,
        public readonly ?string $visitRequestDateYear,
        public readonly string $visitRequestTime,
        public readonly ?string $visitRequestAddress,
        public readonly ?string $visitRequestEntranceNumber,
        public readonly ?string $visitRequestComment,
        public readonly ?string $visitRequestCity,
    ) { }
}
