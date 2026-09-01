<?php

namespace App\Http\Actions\Store\CustomerReview;

use App\Http\Requests\Store\CustomerReview\SubmitCustomerReviewRequest;
use App\Services\CustomerReview\CustomerReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class SubmitCustomerReviewAction
{
    public function __invoke(
        SubmitCustomerReviewRequest $request,
        CustomerReviewService $service,
    ): JsonResponse|RedirectResponse {
        $result = $service->submit($request->toDTO());

        if ($request->expectsJson()) {
            return response()->json([
                'success' => $result->isSuccess(),
                'message' => $result->getMessage(),
            ], $result->isSuccess() ? 201 : 500);
        }

        return back()->with(
            $result->isSuccess() ? 'success' : 'error',
            $result->getMessage(),
        )->withFragment('home-reviews-title');
    }
}
