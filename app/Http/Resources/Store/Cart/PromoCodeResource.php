<?php

namespace App\Http\Resources\Store\Cart;

use App\Services\PromoCode\PromoCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoCodeResource extends JsonResource
{
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'code' => $this->resource->code,
            'label' => app(PromoCodeService::class)->label($this->resource),
            'discount_type' => $this->resource->discount_type,
            'discount_value' => $this->resource->effectiveDiscountValue(),
        ];
    }
}
