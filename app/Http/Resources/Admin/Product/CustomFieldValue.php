<?php

namespace App\Http\Resources\Admin\Product;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomFieldValue extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'field_id' => $this->resource['field_id'],
            'value' => $this->resource['value'],
        ];
    }
}
