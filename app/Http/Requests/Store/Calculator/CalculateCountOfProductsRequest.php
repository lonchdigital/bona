<?php

namespace App\Http\Requests\Store\Calculator;

use App\Http\Requests\BaseRequest;
use App\Services\Calculator\DTO\CalculateCountOfProductsDTO;

class CalculateCountOfProductsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'product_id' => [
                'nullable',
                'exists:products,id',
            ],
            'wallpaper_width' => [
                'required',
                'numeric',
            ],
            'wallpaper_length' => [
                'required',
                'numeric',
            ],
            'wall' => [
                'required',
                'array',
                'min:1',
            ],
            'wall.*.height' => [
                'required',
                'numeric',
            ],
            'wall.*.width' => [
                'required',
                'numeric',
            ],
            'window' => [
                'array',
            ],
            'window.*.height' => [
                'required',
                'numeric',
            ],
            'window.*.width' => [
                'required',
                'numeric',
            ],
            'door' => [
                'array',
            ],
            'door.*.height' => [
                'required',
                'numeric',
            ],
            'door.*.width' => [
                'required',
                'numeric',
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'wallpaper_width' => mb_strtolower(trans('base.wallpaper_width')),
            'wallpaper_length' => mb_strtolower(trans('base.wallpaper_length')),
            'wall.*.height' => mb_strtolower(trans('base.wall_height')),
            'wall.*.width' => mb_strtolower(trans('base.wall_width')),
            'window.*.height' => mb_strtolower(trans('base.window_height')),
            'window.*.width' => mb_strtolower(trans('base.window_width')),
            'door.*.height' => mb_strtolower(trans('base.door_height')),
            'door.*.width' => mb_strtolower(trans('base.door_width')),
        ];
    }

    public function toDTO(): CalculateCountOfProductsDTO
    {
        return new CalculateCountOfProductsDTO(
            $this->input('product_id'),
            $this->input('wallpaper_width'),
            $this->input('wallpaper_length'),
            $this->input('wall'),
            $this->input('window'),
            $this->input('door'),
        );
    }
}
