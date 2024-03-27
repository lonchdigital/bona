<?php

namespace App\Http\Actions\EmailSubscription;

use App\Http\Actions\Admin\BaseAction;
use App\Services\EmailSubscription\EmailSubscriptionService;

class ConfirmSubscriptionAction extends BaseAction
{
    public function __invoke(string $emailSubscriptionCode, EmailSubscriptionService $emailSubscriptionService)
    {
        $result = $emailSubscriptionService->confirmSubscription($emailSubscriptionCode);

        if ($result->isSuccess()) {
            return view('pages.email-subscription.success', [
                'successMessage' => $result->getMessage(),
            ]);
        } else {
            return view('pages.email-subscription.fail', [
                'errorMessage' => $result->getMessage(),
            ]);
        }
    }
}
