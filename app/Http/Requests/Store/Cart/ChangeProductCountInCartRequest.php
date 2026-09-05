<?php

namespace App\Http\Requests\Store\Cart;

use App\Http\Requests\BaseRequest;
use App\Services\Cart\DTO\ChangeProductCountInCartDTO;

class ChangeProductCountInCartRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'product_count' => [
                'required',
                'integer',
                // Nothing below one is a quantity. Without this the endpoint
                // accepted zero, and a negative number would have been carried
                // into the order total as a discount.
                'min:1',
            ],
            'product_attributes' => [
                'nullable',
                'array',
            ],
            'cart_line_id' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'bundle_key' => [
                'nullable',
                'uuid',
            ],
            /*'product_attributes_price' => [
                'nullable',
                'integer',
            ],*/
        ];
    }

    public function toDTO(): ChangeProductCountInCartDTO
    {
        return new ChangeProductCountInCartDTO(
            $this->input('product_count'),
            $this->input('product_attributes'),
            $this->integer('cart_line_id') ?: null,
            $this->input('bundle_key'),
        );
    }
}
