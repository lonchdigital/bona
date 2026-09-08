<?php

namespace App\Services\EmailService;

use App\Mail\OrderCountDoors;
use App\Models\VisitRequest;
use App\Services\Base\BaseService;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailService\DTO\OrderCountDoorsDTO;
use App\Services\Telegram\TelegramNotificationService;
use Illuminate\Support\Facades\Mail;

class OrderCountDoorsService extends BaseService
{
    public function __construct(private readonly TelegramNotificationService $telegram) {}

    public function orderCountDoors(OrderCountDoorsDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use ($request) {

            $description = collect([
                $request->currentProductTitle,
            ])->filter()->implode("\n");

            $lead = VisitRequest::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'status_id' => 1,
                'form_title' => $request->title,
                'description' => $description ?: null,
                'source_url' => $request->sourceUrl,
            ]);

            if (config('domain.admin_notification_emails')) {
                foreach (explode(',', config('domain.admin_notification_emails')) as $email) {
                    Mail::to($email)->send(new OrderCountDoors($request->title, $request->name, $request->phone, $request->currentProductTitle, $request->currentProductUrl));
                }
            }

            $this->telegram->notifyLead(
                $lead,
                $description,
                $request->sourceUrl,
                ['Товар' => $request->currentProductTitle],
            );

            return ServiceActionResult::make(true, trans('base.subscription_email_sent'));
        });
    }
}
