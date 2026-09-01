<?php

namespace App\Http\Requests\Store\Email;

use App\Http\Requests\BaseRequest;
use App\Services\EmailService\DTO\UserChooseDoorsDTO;

class UserChooseDoorsRequest extends BaseRequest
{
    public function rules(): array
    {
        return [
            'title' => ['nullable', 'string', 'max:191'],
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'phone' => ['required', 'string', 'regex:/^\+38 \(0\d{2}\) \d{3} \d{2} \d{2}$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'agree' => ['accepted'],
            'website' => ['nullable', 'size:0'],
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

    public function toDTO(): UserChooseDoorsDTO
    {
        return new UserChooseDoorsDTO(
            $this->input('title'),
            $this->input('name'),
            $this->input('phone'),
            $this->input('description'),
            $this->input('agree'),
        );
    }
}
