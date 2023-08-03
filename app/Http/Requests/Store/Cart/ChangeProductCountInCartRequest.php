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
            ]
        ];
    }


    public function toDTO(): ChangeProductCountInCartDTO
    {
        return new ChangeProductCountInCartDTO(
            $this->input('product_count'),
        );
    }
}
