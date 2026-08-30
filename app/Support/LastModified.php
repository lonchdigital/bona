<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;
use Illuminate\Http\Request;

final class LastModified
{
    private const REQUEST_ATTRIBUTE = 'response_last_modified';

    public static function set(DateTimeInterface|string|null $value): void
    {
        if ($value === null) {
            return;
        }

        request()->attributes->set(
            self::REQUEST_ATTRIBUTE,
            CarbonImmutable::parse($value),
        );
    }

    public static function get(Request $request): ?DateTimeInterface
    {
        $value = $request->attributes->get(self::REQUEST_ATTRIBUTE);

        return $value instanceof DateTimeInterface ? $value : null;
    }
}
