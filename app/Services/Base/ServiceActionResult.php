<?php

namespace App\Services\Base;

class ServiceActionResult
{
    public function __construct(
        private bool $isSuccess,
        private ?string $message
    )
    { }

    public static function make(bool $isSuccess, ?string $message = null): ServiceActionResult
    {
        return new ServiceActionResult($isSuccess, $message);
    }

    public function success(): ServiceActionResult
    {
        $this->isSuccess = true;
        return $this;
    }

    public function fail(): ServiceActionResult
    {
        $this->isSuccess = false;
        return $this;
    }

    public function setMessage(string $message): ServiceActionResult
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }
}
