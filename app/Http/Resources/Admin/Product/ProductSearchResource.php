<?php

namespace App\Http\Resources\Admin\Product;

use App\Http\Resources\Admin\Collection\CollectionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductSearchResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'text' => $this->resource['name'] . ' ' . $this->resource['sku'],
        ];
    }
}
