<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class RequiredImageDeletedRule implements ValidationRule
{
    public function __construct(
        public readonly string $baseFieldName,
    ) { }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value) {
            $fail('validation.required')->translate([
                'attribute' => $this->baseFieldName,
            ]);
        }
    }
}
