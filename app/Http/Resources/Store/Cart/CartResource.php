<?php

namespace App\Http\Resources\Store\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'products' => CartProductResource::collection($this->resource['products']),
            'summary' => $this->resource['summary'],
            'has_free_delivery' => $this->resource['has_free_delivery'],
            'promo_code' => $this->resource['promo_code'] ? PromoCodeResource::make($this->resource['promo_code']) : null,
        ];
    }
}
