<?php

namespace App\Http\Resources\Store\Cart;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartSummaryWithDelivery extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'products' => $this->resource['summary']['products'],
            'total' => $this->resource['summary']['total'],
            'discount' => $this->resource['summary']['discount'],
            'is_carrier' => $this->resource['summary']['is_carrier'],
            'delivery_old' => $this->resource['summary']['delivery_old'],
            'delivery' => $this->resource['summary']['delivery'],
        ];
    }
}
