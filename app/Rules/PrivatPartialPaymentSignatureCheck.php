<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class PrivatPartialPaymentSignatureCheck implements DataAwareRule, ValidationRule
{
    protected array $data = [];

    /**
     * Run the validation rule.
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $store_id = (string) config('payment.privatbank.store_id');
        $store_password = (string) config('payment.privatbank.password');
        if ($store_id === '' || $store_password === '') {
            $fail('validation.signature_check')->translate();

            return;
        }

        if (! hash_equals($store_id, (string) ($this->data['storeId'] ?? ''))) {
            $fail('validation.signature_check')->translate();

            return;
        }

        $sign = base64_encode(sha1(
            $store_password.
            $store_id.
            ($this->data['orderId'] ?? '').
            ($this->data['paymentState'] ?? '').
            ($this->data['message'] ?? '').
            $store_password, 1
        ));
        if (! is_string($value) || ! hash_equals($sign, $value)) {
            $fail('validation.signature_check')->translate();
        }
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
