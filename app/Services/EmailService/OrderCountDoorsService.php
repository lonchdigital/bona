<?php

namespace App\Services\EmailService;

use App\Mail\OrderCountDoors;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailService\DTO\OrderCountDoorsDTO;
use Illuminate\Support\Facades\Mail;

class OrderCountDoorsService extends BaseService
{
    public function orderCountDoors(OrderCountDoorsDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new OrderCountDoors($request->title, $request->name, $request->phone, $request->currentProductTitle, $request->currentProductUrl));
                }
            }

            return ServiceActionResult::make(true, trans('base.subscription_email_sent'));
        });
    }
}
