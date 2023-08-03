<?php

namespace App\Services\Calculator;

use App\Models\Product;
use App\Services\Base\BaseService;
use App\Services\Calculator\DTO\CalculateCountOfProductsDTO;

class CalculatorService extends BaseService
{
    public function calculate(CalculateCountOfProductsDTO $request): array
    {
        $product = null;

        if ($request->productId) {
            $product = Product::find($request->productId);
        }

        $productRapport = 0;
        $productRapportField = $product->productType->fields->where('slug', 'raport')->first();
        if ($productRapportField && $product) {
            $productRapport = intval($product->getCustomFieldValue($productRapportField->id));
        }

        $totalCountOfRolls = 0;
        foreach ($request->walls as $wall) {
            $countOfLinesPerWall = ceil($wall['width'] / ($request->wallpaperWidth * 100));
            $lineHeight = $wall['height'] + $productRapport + 5;
            $countOfLinesPerWallpaper = (($request->wallpaperLength * 100) / $lineHeight);
            $totalCountOfRolls += ceil($countOfLinesPerWall / $countOfLinesPerWallpaper);
        }

        //calculate count of lines


        //area in centimeters
        $rollArea = $request->wallpaperLength * $request->wallpaperWidth;

        $wallsArea = 0;
        foreach ($request->walls as $wall) {
            $wallsArea += ($wall['height'] * $wall['width']);
        }

        $windowsArea = 0;
        if ($request->windows) {
            foreach ($request->windows as $window) {
                $windowsArea += ($window['height'] * $window['width']);
            }
        }

        $doorsArea = 0;
        if ($request->doors) {
            foreach ($request->doors as $door) {
                $doorsArea += ($door['height'] * $door['width']);
            }
        }

        $wallsArea = ($wallsArea - $windowsArea - $doorsArea) / 10000;

        $countOfRolls = ceil($wallsArea / $rollArea);

        $rollsArea = ($countOfRolls * $rollArea);

        return [
            'product' => $product,
            'count_of_rolls' => $totalCountOfRolls,
            'area_of_rolls' => round($rollsArea, 1),
            'area_required' => round($wallsArea, 1),
        ];
    }
}
