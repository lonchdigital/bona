<?php

namespace App\Http\Resources\Admin\Color;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ColorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource['id'],
            'hex' => $this->resource['hex'],
            'text' => $this->resource['name'],
            'name' => $this->resource['name'],
        ];
    }
}
