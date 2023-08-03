<?php

namespace App\Http\Actions\Admin\VisitRequests;

use App\Models\VisitRequest;
use App\Http\Actions\Admin\BaseAction;
use App\Services\VisitRequest\VisitRequestService;
use App\Http\Requests\Admin\VisitRequest\VisitRequestEditRequest;

class VisitRequestEditAction extends BaseAction
{
    public function __invoke(VisitRequest $visitRequest, VisitRequestEditRequest $request, VisitRequestService $visitRequestService)
    {
        return $this->handleActionResult(
            route('admin.visit-request.list.page'),
            $request,
            $visitRequestService->editVisitRequest($visitRequest, $request->toDto())
        );
    }
}
