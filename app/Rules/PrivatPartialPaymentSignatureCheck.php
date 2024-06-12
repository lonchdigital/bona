<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Log;

class PrivatPartialPaymentSignatureCheck implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $store_id = env('PRIVATBANK_STORE_ID');
        $store_password = env('PRIVATBANK_PASSWORD');
        $sign = base64_encode( sha1(
            $store_password .
            $store_id .
            $this->data['orderId'].
            $this->data['paymentState'] .
            $this->data['message'].
            $store_password, 1
        ));
        if ($sign !== $value) {
            $fail('validation.signature_check')->translate();
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
