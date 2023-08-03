<?php

namespace App\Http\Resources\Store\Calculator;

use App\Http\Resources\BaseProductResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CalculatorResultResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product' => BaseProductResource::make($this->resource['product']),
            'count_of_rolls' => $this->resource['count_of_rolls'],
            'area_of_rolls' => $this->resource['area_of_rolls'],
            'area_required' => $this->resource['area_required'],
        ];
    }
}
