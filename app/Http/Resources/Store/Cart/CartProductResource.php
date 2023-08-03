<?php

namespace App\Http\Resources\Store\Cart;

use App\Http\Resources\BaseProductResource;
use Illuminate\Http\Request;

class CartProductResource extends BaseProductResource
{
    public function toArray(Request $request): array
    {
        $existingMapping = parent::toArray($request);

        $existingMapping['count'] = $this->resource->pivot->count;
        $existingMapping['price'] = round($this->resource->pivot->count * $this->resource->pivot->price, 2);
        $existingMapping['price_per_product'] = $this->resource->price;

        return $existingMapping;
    }
}
