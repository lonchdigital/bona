<?php

namespace App\Http\Requests\Store\Email;

use App\Http\Requests\BaseRequest;
use App\Services\EmailService\DTO\OrderCountDoorsDTO;

class OrderCountDoorsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string'],
            'name' => ['required', 'string'],
            'phone' => ['required',
                'string',
                'regex:/^[^_]*$/',
                'min:16'],
            'agree' => ['accepted'],
            'current_product_title' => ['nullable', 'string'],
            'current_product_url' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        $attributes = [
            'title' => mb_strtolower(trans('base.title')),
            'name' => mb_strtolower(trans('base.name')),
            'phone' => mb_strtolower(trans('base.phone')),
            'agree' => mb_strtolower(trans('base.agree')),
        ];

        return $attributes;
    }

    public function messages()
    {
        return [
            'phone.digits' => trans('base.phone_validation'),
        ];
    }

    public function toDTO(): OrderCountDoorsDTO
    {
        return new OrderCountDoorsDTO(
            $this->input('title'),
            $this->input('name'),
            $this->input('phone'),
            $this->input('agree'),
            $this->input('current_product_title'),
            $this->input('current_product_url'),
        );
    }
}
