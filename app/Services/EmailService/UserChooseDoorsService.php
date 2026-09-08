<?php

namespace App\Services\EmailService;

use App\Mail\UserChooseDoors;
use App\Models\VisitRequest;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailService\DTO\UserChooseDoorsDTO;
use App\Services\Telegram\TelegramNotificationService;
use Illuminate\Support\Facades\Mail;

class UserChooseDoorsService extends BaseService
{
    public function __construct(private readonly TelegramNotificationService $telegram) {}

    public function userChooseDoors(UserChooseDoorsDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            $lead = VisitRequest::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'status_id' => 1,
                'form_title' => $request->title,
                'description' => $request->description,
                'source_url' => $request->sourceUrl,
            ]);

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new UserChooseDoors($request->title, $request->name, $request->phone, $request->description));
                }
            }

            $this->telegram->notifyLead(
                $lead,
                $request->description,
                $request->sourceUrl,
            );

            return ServiceActionResult::make(true, trans('base.subscription_email_sent'));
        });
    }
}
