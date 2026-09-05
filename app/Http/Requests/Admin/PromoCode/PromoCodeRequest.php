<?php

namespace App\Http\Requests\Admin\PromoCode;

use App\Models\PromoCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PromoCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => mb_strtoupper(trim((string) $this->input('code'))),
            'is_active' => $this->boolean('is_active'),
            'all_products' => $this->boolean('all_products'),
            'usage_limit' => $this->filled('usage_limit') ? $this->input('usage_limit') : null,
            'max_discounted_items' => $this->filled('max_discounted_items') ? $this->input('max_discounted_items') : null,
            'maximum_discount_amount' => $this->filled('maximum_discount_amount') ? $this->input('maximum_discount_amount') : null,
            'starts_at' => $this->filled('starts_at') ? $this->input('starts_at') : null,
            'expires_at' => $this->filled('expires_at') ? $this->input('expires_at') : null,
        ]);
    }

    public function rules(): array
    {
        $promoCode = $this->route('promoCode');

        return [
            'code' => [
                'required',
                'string',
                'max:64',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('promo_codes', 'code')->ignore($promoCode?->id),
            ],
            'discount_type' => ['required', Rule::in([PromoCode::TYPE_PERCENT, PromoCode::TYPE_FIXED])],
            'discount_value' => [
                'required',
                'numeric',
                'gt:0',
                Rule::when($this->input('discount_type') === PromoCode::TYPE_PERCENT, ['lte:100']),
            ],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'expires_at' => [
                'nullable',
                'date',
                Rule::when($this->filled('starts_at'), ['after:starts_at']),
            ],
            'usage_limit' => ['nullable', 'integer', 'min:1'],
            'max_discounted_items' => ['nullable', 'integer', 'min:1'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'gt:0'],
            'all_products' => ['required', 'boolean'],
            'product_ids' => ['nullable', 'required_if:all_products,0', 'array'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
        ];
    }

    public function payload(): array
    {
        return $this->validated();
    }
}
