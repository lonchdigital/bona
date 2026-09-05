<?php

namespace App\Http\Requests\Admin\PromoCode;

use App\Models\PromoCode;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'distinct', 'exists:products,id'],
            'brand_ids' => ['nullable', 'array'],
            'brand_ids.*' => ['integer', 'distinct', 'exists:brands,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['integer', 'distinct', 'exists:categories,id'],
            'product_type_ids' => ['nullable', 'array'],
            'product_type_ids.*' => ['integer', 'distinct', 'exists:product_types,id'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($this->boolean('all_products')) {
                return;
            }

            $hasTarget = collect([
                $this->input('product_ids', []),
                $this->input('brand_ids', []),
                $this->input('category_ids', []),
                $this->input('product_type_ids', []),
            ])->flatten()->filter()->isNotEmpty();

            if (! $hasTarget) {
                $validator->errors()->add('scope', trans('admin.promo_code_scope_required'));
            }
        });
    }

    public function payload(): array
    {
        return $this->validated();
    }
}
