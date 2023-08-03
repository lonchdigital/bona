<?php

namespace App\Http\Resources\Admin\SEO;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->pivot->filter_name,
            'field_type_id' => $this->resource->field_type_id,
            'is_multiselectable' => $this->resource->is_multiselectable,
            'options' => ListResource::collection($this->resource->options),
        ];
    }
}
