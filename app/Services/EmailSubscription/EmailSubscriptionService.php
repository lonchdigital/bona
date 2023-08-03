<?php

namespace App\Services\EmailSubscription;

use App\Mail\EmailSubscriptionConfirmedEmail;
use App\Models\EmailSubscription;
use App\Models\PromoCode;
use App\Services\Base\BaseService;
use App\Mail\EmailSubscriptionEmail;
use Illuminate\Support\Facades\Mail;
use App\Services\Base\ServiceActionResult;
use App\Services\EmailSubscription\DTO\SubscribeEmailDTO;

class EmailSubscriptionService extends BaseService
{
    public function subscribe(SubscribeEmailDTO $request): ServiceActionResult
    {
        return $this->coverWithDBTransaction(function () use($request) {

            $code = \Str::random(10) . '-' . \Str::random(2);

            EmailSubscription::create([
                'email' => $request->email,
                'confirmation_code' => $code,
            ]);

            Mail::to($request->email)->send(new EmailSubscriptionEmail($code));

            return ServiceActionResult::make(true, trans('base.subscription_email_sent'));
        });
    }

    public function confirmSubscription(string $emailSubscriptionCode): ServiceActionResult
    {
        $emailSubscription = EmailSubscription::where('confirmation_code', $emailSubscriptionCode)->first();

        if (!$emailSubscription || $emailSubscription->confirmed_at != null) {
            return ServiceActionResult::make(false, trans('base.email_confirmed_fail_text'));
        }

        return $this->coverWithDBTransaction(function () use($emailSubscription) {
            $emailSubscription->update([
                'confirmed_at' => now(),
            ]);

            $latestCodeId = PromoCode::latest()->first();
            if (!$latestCodeId) {
                $latestCodeId = 1;
            } else {
                $latestCodeId = $latestCodeId->id;
            }

            $code = \Str::random(8) . '-' . $latestCodeId;

            PromoCode::create([
                'code' => $code,
                'discount' => 15,
            ]);

            Mail::to($emailSubscription->email)->send(new EmailSubscriptionConfirmedEmail($code));

            return ServiceActionResult::make(true, trans('base.email_confirmed_thank_you'));
        });




    }
}
