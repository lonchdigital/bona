<?php

namespace App\Http\Actions\Store\Mail;

use App\Http\Requests\Store\Email\UserChooseDoorsRequest;
use App\Services\EmailService\UserChooseDoorsService;
use Illuminate\Http\JsonResponse;

class UserChooseDoorsAction
{
    public function __invoke(
        UserChooseDoorsRequest $request,
        //        Request $request,
        UserChooseDoorsService $userChooseDoorsService
    ): JsonResponse {
        $result = $userChooseDoorsService->userChooseDoors($request->toDTO());

        return response()->json([
            'success' => $result->isSuccess(),
            'message' => $result->getMessage(),
        ], $result->isSuccess() ? 200 : 500);
    }
}
