<?php

namespace App\Http\Resources\Store\Product;

use App\Http\Resources\BaseProductResource;
use Illuminate\Http\Request;

class ProductSearchResource extends BaseProductResource
{
    public function toArray(Request $request): array
    {
        $fields = parent::toArray($request);
        $fields['width'] = $this->resource->width;
        $fields['length'] = $this->resource->length;

        return $fields;
    }
}
