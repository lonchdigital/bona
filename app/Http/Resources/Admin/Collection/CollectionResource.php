<?php

namespace App\Http\Resources\Admin\Collection;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'text' => $this->resource['name'],
            'brand_id' => $this->resource['brand_id'],
            'name' => $this->resource['name'],
        ];
    }
}
