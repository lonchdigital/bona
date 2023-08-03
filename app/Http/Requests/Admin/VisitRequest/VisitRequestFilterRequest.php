<?php

namespace App\Http\Requests\Admin\VisitRequest;

use App\Http\Requests\BaseRequest;
use App\DataClasses\VisitRequestStatusesDataClass;
use App\Services\VisitRequest\DTO\VisitRequestFilterDTO;

class VisitRequestFilterRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'status_id' => [
                'nullable',
                'in:'. VisitRequestStatusesDataClass::get()->pluck('id')->implode(',')
            ],
        ];
    }

    public function toDTO(): VisitRequestFilterDTO
    {
        return new VisitRequestFilterDTO(
            $this->input('status_id')
        );
    }
}
