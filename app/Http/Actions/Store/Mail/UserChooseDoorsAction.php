<?php

namespace App\Http\Actions\Store\Mail;


use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Store\Email\UserChooseDoorsRequest;
use App\Services\EmailService\UserChooseDoorsService;


class UserChooseDoorsAction extends BaseAction
{
    public function __invoke(
        UserChooseDoorsRequest $request,
        UserChooseDoorsService $userChooseDoorsService
    )
    {
        $result = $userChooseDoorsService->userChooseDoors($request->toDTO());
        return json_encode($result);
    }
}
