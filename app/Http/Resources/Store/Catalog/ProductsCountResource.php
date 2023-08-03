<?php

namespace App\Http\Resources\Store\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductsCountResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'count' => $this->resource['count'],
        ];
    }
}
