<?php

namespace App\Http\Requests\Admin\VisitRequest;

use App\DataClasses\VisitRequestStatusesDataClass;
use App\Http\Requests\BaseRequest;
use App\Services\VisitRequest\DTO\VisitRequestEditDTO;

class VisitRequestEditRequest extends BaseRequest
{
    public function rules(): array {
        return [
            'status_id' => [
                'required',
                'integer',
                'in:' . VisitRequestStatusesDataClass::get()->pluck('id')->implode(','),
            ]
        ];
    }

    public function toDto(): VisitRequestEditDTO
    {
        return new VisitRequestEditDTO(
            $this->input('status_id')
        );
    }
}
