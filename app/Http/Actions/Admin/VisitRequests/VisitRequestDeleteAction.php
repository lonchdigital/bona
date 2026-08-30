<?php

namespace App\Http\Actions\Admin\VisitRequests;

use App\Http\Actions\Admin\BaseAction;
use App\Models\VisitRequest;
use App\Services\VisitRequest\VisitRequestService;
use Illuminate\Http\Request;

class VisitRequestDeleteAction extends BaseAction
{
    public function __invoke(VisitRequest $visitRequest, Request $request, VisitRequestService $visitRequestService)
    {
        $result = $visitRequestService->deleteVisitRequest($visitRequest);

        return $this->handleActionResult(route('admin.visit-request.list.page'), $request, $result);
    }
}
