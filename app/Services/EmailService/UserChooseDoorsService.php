<?php

namespace App\Services\EmailService;

use App\Mail\UserChooseDoors;
use App\Models\VisitRequest;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailService\DTO\UserChooseDoorsDTO;
use Illuminate\Support\Facades\Mail;

class UserChooseDoorsService extends BaseService
{
    public function userChooseDoors(UserChooseDoorsDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            VisitRequest::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'status_id' => 1,
                'form_title' => $request->title,
            ]);

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new UserChooseDoors($request->title, $request->name, $request->phone, $request->description));
                }
            }

            return ServiceActionResult::make(true, trans('base.subscription_email_sent'));
        });
    }
}
