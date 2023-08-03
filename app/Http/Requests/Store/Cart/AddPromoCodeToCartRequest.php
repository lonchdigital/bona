<?php

namespace App\Http\Requests\Store\Cart;

use App\Http\Requests\BaseRequest;
use App\Services\Cart\DTO\AddPromoCodeToCartDTO;

class AddPromoCodeToCartRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'code' => [
                'required',
                'exists:promo_codes,code',
            ]
        ];
    }

    public function attributes(): array
    {
        return [
            'code' => mb_strtolower(trans('base.your_promo_code')),
        ];
    }

    public function toDTO(): AddPromoCodeToCartDTO
    {
        return new AddPromoCodeToCartDTO(
            $this->input('code'),
        );
    }
}
