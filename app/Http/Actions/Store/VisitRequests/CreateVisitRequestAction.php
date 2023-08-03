<?php

namespace App\Http\Actions\Store\VisitRequests;

use App\Http\Actions\Admin\BaseAction;
use App\Services\VisitRequest\VisitRequestService;
use App\Http\Requests\Store\VisitRequest\VisitRequestCreateRequest;

class CreateVisitRequestAction extends BaseAction
{
    public function __invoke(
        VisitRequestCreateRequest $request,
        VisitRequestService $visitRequestService,
    )
    {
        $visitRequestService->createVisitRequest($request->toDTO());

        return redirect()->back()->with(['modal_success' => true]);
    }
}
