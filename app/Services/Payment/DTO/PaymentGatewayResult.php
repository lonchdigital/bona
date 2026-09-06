<?php

namespace App\Services\Payment\DTO;

final readonly class PaymentGatewayResult
{
    private function __construct(
        public bool $successful,
        public array $data,
        public ?string $message,
        public ?int $statusCode,
        public ?string $traceId,
    ) {}

    public static function success(array $data = [], ?int $statusCode = null, ?string $traceId = null): self
    {
        return new self(true, $data, null, $statusCode, $traceId);
    }

    public static function failure(
        ?string $message = null,
        ?int $statusCode = null,
        ?string $traceId = null,
        array $data = [],
    ): self {
        return new self(false, $data, $message, $statusCode, $traceId);
    }
}
