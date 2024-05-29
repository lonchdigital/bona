<?php

namespace App\Http\Actions\Admin\VisitRequests;

use App\Models\VisitRequest;
use Illuminate\Http\Request;
use App\Http\Actions\Admin\BaseAction;
use App\Services\VisitRequest\VisitRequestService;


class VisitRequestDeleteAction extends BaseAction
{
    public function __invoke(VisitRequest $visitRequest, Request $request, VisitRequestService $visitRequestService)
    {
        $result = $visitRequestService->deleteVisitRequest($visitRequest);

        return $this->handleActionResult(route('admin.visit-request.list.page'), $request, $result);
    }
}
