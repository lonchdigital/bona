<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BaseListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'value' => $this->resource['value'],
            'text' => $this->resource['text'],
        ];
    }
}
