<?php

namespace App\Http\Requests\EmailSubscription;

use App\Http\Requests\BaseRequest;
use App\Services\EmailSubscription\DTO\SubscribeEmailDTO;

class SubscribeEmailRequest extends BaseRequest
{
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                'unique:email_subscriptions,email'
            ],
        ];
    }


    public function toDTO(): SubscribeEmailDTO
    {
        return new SubscribeEmailDTO(
            $this->input('email'),
        );
    }
}
