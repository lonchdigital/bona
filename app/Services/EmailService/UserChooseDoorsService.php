<?php

namespace App\Services\EmailService;

use App\Mail\UserChooseDoors;
use App\Models\EmailSubscription;
use App\Services\Base\BaseService;
use App\Mail\EmailSubscriptionEmail;
use Illuminate\Support\Facades\Mail;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailService\DTO\UserChooseDoorsDTO;

class UserChooseDoorsService extends BaseService
{
    public function userChooseDoors(UserChooseDoorsDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send( new UserChooseDoors($request->title, $request->name, $request->phone) );
                }
            }
            return ServiceActionResult::make(true, trans('base.subscription_email_sent'));
        });
    }


}
