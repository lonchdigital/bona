<?php

namespace App\Http\Actions\Store\Mail;


use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Email\OrderCountDoorsRequest;
use App\Services\EmailService\OrderCountDoorsService;
use Illuminate\Http\Request;


class OrderCountDoorsAction extends BaseAction
{
    public function __invoke(
        OrderCountDoorsRequest $request,
        OrderCountDoorsService $orderCountDoorsService
    )
    {
        $result = $orderCountDoorsService->orderCountDoors($request->toDTO());
        return json_encode($result);
    }
}
