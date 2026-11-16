<?php

namespace App\Http\Actions\Store\Mail;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Email\OrderCountDoorsRequest;
use App\Services\EmailService\OrderCountDoorsService;
use App\Services\SerpAgent\SerpAgentConversionService;

class OrderCountDoorsAction extends BaseAction
{
    public function __invoke(
        OrderCountDoorsRequest $request,
        OrderCountDoorsService $orderCountDoorsService,
        SerpAgentConversionService $conversions,
    ) {
        $result = $orderCountDoorsService->orderCountDoors($request->toDTO());

        if ($result->isSuccess()) {
            $conversions->reportAccepted($request, 'order-request');
        }

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
        ], $result->isSuccess() ? 200 : 500);
    }
}
