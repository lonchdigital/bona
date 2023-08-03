<?php

namespace App\Http\Resources\Store\Product;

use App\DataClasses\ProductSpecialOfferOptionsDataClass;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSpecialOfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource,
            'name' => ProductSpecialOfferOptionsDataClass::get($this->resource)['name'],
        ];
    }
}
