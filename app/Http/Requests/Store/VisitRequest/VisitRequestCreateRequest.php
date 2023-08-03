<?php

namespace App\Http\Requests\Store\VisitRequest;

use App\Http\Requests\BaseRequest;
use App\DataClasses\VisitRequestTypeDataClass;
use App\Services\VisitRequest\DTO\VisitRequestCreateDTO;

class VisitRequestCreateRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules = [
            'modal_type_id' => [
                'required',
                'integer',
                'in:' . VisitRequestTypeDataClass::get()->pluck('id')->implode(','),
            ]
        ];

        $rules['visit_request_name'] = [
            'required',
            'string'
        ];

        $rules['visit_request_phone'] = [
            'required',
            'string'
        ];

        if ($this->input('modal_type_id') == VisitRequestTypeDataClass::SHOWROOM_VISIT
        ||$this->input('modal_type_id') == VisitRequestTypeDataClass::DESIGNER_APPOINTMENT) {
            $rules['visit_request_email'] = [
                'required',
                'email',
            ];

            $rules['visit_request_date_day'] = [
                'required',
                'string'
            ];

            $rules['visit_request_date_month'] = [
                'required',
                'string'
            ];

            $rules['visit_request_date_year'] = [
                'required',
                'string'
            ];

            $rules['visit_request_time_hours'] = [
                'required',
                'string'
            ];

            $rules['visit_request_time_minutes'] = [
                'required',
                'string'
            ];
        }

        if ($this->input('modal_type_id') == VisitRequestTypeDataClass::DESIGNER_APPOINTMENT) {
            $rules['visit_request_address'] = [
                'required',
                'string',
            ];

            $rules['visit_request_entrance_number'] = [
                'required',
                'string',
            ];

            $rules['visit_request_comment'] = [
                'nullable',
                'string',
            ];
        }

        if ($this->input('modal_type_id') == VisitRequestTypeDataClass::SHOWROOM_TAXI) {
            $rules['visit_request_time_hours'] = [
                'required',
                'string'
            ];

            $rules['visit_request_time_minutes'] = [
                'required',
                'string'
            ];

            $rules['visit_request_city'] = [
                'required',
                'string'
            ];

            $rules['visit_request_address'] = [
                'required',
                'string',
            ];

            $rules['visit_request_entrance_number'] = [
                'required',
                'string',
            ];

            $rules['visit_request_comment'] = [
                'nullable',
                'string',
            ];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'visit_request_name' => mb_strtolower(trans('base.name')),
            'visit_request_phone' => mb_strtolower(trans('base.phone')),
            'visit_request_email' => mb_strtolower(trans('base.email')),
            'visit_request_date_day' => mb_strtolower(trans('base.day')),
            'visit_request_date_month' => mb_strtolower(trans('base.month')),
            'visit_request_date_year' => mb_strtolower(trans('base.year')),
            'visit_request_time_hours' => mb_strtolower(trans('base.visit_time_hours')),
            'visit_request_time_minutes' => mb_strtolower(trans('base.visit_time_minutes')),
            'visit_request_address' => mb_strtolower(trans('base.address')),
            'visit_request_entrance_number' => mb_strtolower(trans('base.entrance_number')),
            'visit_request_comment' => mb_strtolower(trans('base.comment')),
            'visit_request_city' => mb_strtolower(trans('base.city')),
        ];
    }

    public function toDTO(): VisitRequestCreateDTO
    {
        return new VisitRequestCreateDTO(
            requestTypeId: $this->input('modal_type_id'),
            visitRequestName: $this->input('visit_request_name'),
            visitRequestPhone: $this->input('visit_request_phone'),
            visitRequestEmail: $this->input('visit_request_email'),
            visitRequestDateDay: $this->input('visit_request_date_day') ?? null,
            visitRequestDateMonth: $this->input('visit_request_date_month') ?? null,
            visitRequestDateYear: $this->input('visit_request_date_year') ?? null,
            visitRequestTime: $this->input('visit_request_time_hours') . ':' . $this->input('visit_request_time_minutes') ,
            visitRequestAddress: $this->input('visit_request_address') ?? null,
            visitRequestEntranceNumber: $this->input('visit_request_entrance_number') ?? null,
            visitRequestComment: $this->input('visit_request_comment') ?? null,
            visitRequestCity: $this->input('visit_request_city') ?? null,

        );
    }
}
