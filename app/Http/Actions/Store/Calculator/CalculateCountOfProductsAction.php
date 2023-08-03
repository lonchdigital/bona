<?php

namespace App\Http\Actions\Store\Calculator;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Resources\Store\Calculator\CalculatorResultResource;
use App\Services\Calculator\CalculatorService;
use App\Http\Requests\Store\Calculator\CalculateCountOfProductsRequest;

class CalculateCountOfProductsAction extends BaseAction
{
    public function __invoke(CalculateCountOfProductsRequest $request, CalculatorService $calculatorService)
    {
        return CalculatorResultResource::make($calculatorService->calculate($request->toDTO()));
    }
}
