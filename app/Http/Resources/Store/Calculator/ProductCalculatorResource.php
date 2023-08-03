<?php

namespace App\Http\Resources\Store\Calculator;

use App\Http\Resources\BaseProductResource;
use Illuminate\Http\Request;

class ProductCalculatorResource extends BaseProductResource
{
    public function toArray(Request $request): array
    {
        $fields = parent::toArray($request);
        $fields['width'] = $this->resource->width;
        $fields['length'] = $this->resource->length;

        return $fields;
    }
}
