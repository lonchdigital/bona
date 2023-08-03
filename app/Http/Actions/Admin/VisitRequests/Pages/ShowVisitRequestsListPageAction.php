<?php

namespace App\Http\Actions\Admin\VisitRequests\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\VisitRequest\VisitRequestFilterRequest;
use App\Services\VisitRequest\VisitRequestService;

class ShowVisitRequestsListPageAction extends BaseAction
{
    public function __invoke(
        VisitRequestFilterRequest $request,
        VisitRequestService $visitRequestService
    )
    {
        $dto = $request->toDTO();

        return view('pages.admin.visit-requests.list', [
            'visitRequestsPaginated' => $visitRequestService->getVisitRequestsListPaginated($dto),
            'searchData' => $dto,
        ]);
    }
}
