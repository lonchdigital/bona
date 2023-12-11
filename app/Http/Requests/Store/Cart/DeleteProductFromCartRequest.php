<?php

namespace App\Http\Requests\Store\Cart;

use App\Http\Requests\BaseRequest;
use App\Services\Cart\DTO\DeleteProductFromCartDTO;

class DeleteProductFromCartRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'product_attributes' => [
                'nullable',
                'array',
            ]
        ];
    }


    public function toDTO(): DeleteProductFromCartDTO
    {
        return new DeleteProductFromCartDTO(
            $this->input('product_attributes'),
        );
    }
}
