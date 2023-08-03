<?php

namespace App\Services\VisitRequest;

use App\DataClasses\VisitRequestStatusesDataClass;
use App\DataClasses\VisitRequestTypeDataClass;
use App\Mail\AdminNotificationEmail;
use App\Models\VisitRequest;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\VisitRequest\DTO\VisitRequestCreateDTO;
use App\Services\VisitRequest\DTO\VisitRequestEditDTO;
use App\Services\VisitRequest\DTO\VisitRequestFilterDTO;
use Illuminate\Support\Facades\Mail;

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

    public function createVisitRequest(VisitRequestCreateDTO $request): void
    {
        $visitRequest = null;
        if ($request->requestTypeId === VisitRequestTypeDataClass::SHOWROOM_VISIT) {
            $visitRequest = VisitRequest::create([
                'visit_type_id' => $request->requestTypeId,
                'name' => $request->visitRequestName,
                'phone' => $request->visitRequestPhone,
                'email' => $request->visitRequestEmail,
                'visit_date' => $request->visitRequestDateDay . '/' . $request->visitRequestDateMonth . '/' . $request->visitRequestDateYear,
                'visit_time' => $request->visitRequestTime,
                'status_id' => VisitRequestStatusesDataClass::STATUS_NEW,
            ]);
        } elseif ($request->requestTypeId === VisitRequestTypeDataClass::SHOWROOM_TAXI) {
            $visitRequest = VisitRequest::create([
                'visit_type_id' => $request->requestTypeId,
                'name' => $request->visitRequestName,
                'phone' => $request->visitRequestPhone,
                'city' => $request->visitRequestCity,
                'visit_time' => $request->visitRequestTime,
                'address' => $request->visitRequestAddress,
                'entrance_number' => $request->visitRequestEntranceNumber,
                'comment' => $request->visitRequestComment,
                'status_id' => VisitRequestStatusesDataClass::STATUS_NEW,
            ]);
        } elseif ($request->requestTypeId === VisitRequestTypeDataClass::DESIGNER_APPOINTMENT) {
            $visitRequest = VisitRequest::create([
                'visit_type_id' => $request->requestTypeId,
                'name' => $request->visitRequestName,
                'phone' => $request->visitRequestPhone,
                'email' => $request->visitRequestEmail,
                'visit_date' => $request->visitRequestDateDay . '/' . $request->visitRequestDateMonth . '/' . $request->visitRequestDateYear,
                'visit_time' => $request->visitRequestTime,
                'address' => $request->visitRequestAddress,
                'entrance_number' => $request->visitRequestEntranceNumber,
                'comment' => $request->visitRequestComment,
                'status_id' => VisitRequestStatusesDataClass::STATUS_NEW,
            ]);
        }

        if (config('domain.admin_notification_emails') && $visitRequest) {
            foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                Mail::to($email)->send(new AdminNotificationEmail(trans('admin.new_visit_request_email_subject'), route('admin.visit-request.details.page', ['visitRequest' => $visitRequest->id])));
            }
        }
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
}
