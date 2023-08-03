<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PhoneNumberLengthRule implements ValidationRule
{
    public function __construct(
        public readonly int $length
    )
    { }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (strlen(preg_replace('/\(|\)|-|_|\+/', '', $value)) !== $this->length) {
            $fail('validation.phone_number.size')->translate([
                'size' => $this->length,
            ]);
        }
    }
}
