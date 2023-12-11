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
            ],
            'product_attributes' => [
                'nullable',
                'array',
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
//            $this->input('product_attributes_price'),
        );
    }
}
