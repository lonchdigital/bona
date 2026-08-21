<?php

namespace App\Services\SerpAgent\Exceptions;

use RuntimeException;

/**
 * Signals a payload we understood but cannot publish. The HTTP status is
 * carried along so the webhook can answer Serp Agent with something more
 * useful than a generic 500.
 */
class SerpAgentException extends RuntimeException
{
    public function __construct(string $message, private readonly int $status = 422)
    {
        parent::__construct($message);
    }

    public function status(): int
    {
        return $this->status;
    }
}
