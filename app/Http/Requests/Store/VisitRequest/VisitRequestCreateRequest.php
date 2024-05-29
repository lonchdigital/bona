<?php

namespace App\Http\Requests\Store\VisitRequest;

use App\Http\Requests\BaseRequest;
use App\DataClasses\VisitRequestTypeDataClass;
use App\Services\VisitRequest\DTO\VisitRequestCreateDTO;

class VisitRequestCreateRequest extends BaseRequest
{
    public function rules(): array
    {
        $rules['name'] = [
            'required',
            'string'
        ];
        $rules['phone'] = [
            'required',
            'string'
        ];

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'name' => mb_strtolower(trans('base.name')),
            'phone' => mb_strtolower(trans('base.phone')),
        ];
    }

    public function toDTO(): VisitRequestCreateDTO
    {
        return new VisitRequestCreateDTO(
            name: $this->input('name'),
            phone: $this->input('phone')
        );
    }
}
