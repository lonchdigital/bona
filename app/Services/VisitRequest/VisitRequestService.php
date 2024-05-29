<?php

namespace App\Services\VisitRequest;

use App\Models\VisitRequest;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\VisitRequest\DTO\VisitRequestEditDTO;
use App\Services\VisitRequest\DTO\VisitRequestFilterDTO;


class VisitRequestService extends BaseService
{
    public function getVisitRequestsListPaginated(VisitRequestFilterDTO $request)
    {
        $query = VisitRequest::query();

        if ($request->statusId) {
            $query->where('status_id', $request->statusId);
        }

        return $query->paginate(config('domain.items_per_page'));
    }

    public function editVisitRequest(VisitRequest $visitRequest, VisitRequestEditDTO $request): ServiceActionResult
    {
        return $this->coverWithTryCatch(function () use($visitRequest, $request) {
            $visitRequest->update([
                'status_id' => $request->statusId,
            ]);

            return ServiceActionResult::make(true, trans('admin.visit_request_edit_success'));
        });
    }

    public function deleteVisitRequest(VisitRequest $visitRequest): ServiceActionResult
    {
        $visitRequest->delete();

        return ServiceActionResult::make(true, trans('admin.visit_request_delete_success'));
    }

}
