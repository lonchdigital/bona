<?php

namespace App\Http\Actions\EmailSubscription;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\EmailSubscription\SubscribeEmailRequest;
use App\Services\EmailSubscription\EmailSubscriptionService;

class SubscribeEmailAction extends BaseAction
{
    public function __invoke(SubscribeEmailRequest $request, EmailSubscriptionService $emailSubscriptionService)
    {
        $result = $emailSubscriptionService->subscribe($request->toDTO());

        if ($result->isSuccess()) {
            \Session::put(['email_subscription_sent' => true]);
        }

        return $this->handleActionResult('', $request, $result);
    }
}
