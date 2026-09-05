<?php

namespace App\Http\Actions\Admin\PromoCodes;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\PromoCode\PromoCodeRequest;
use App\Services\Base\ServiceActionResult;
use App\Services\PromoCode\PromoCodeService;

class PromoCodeCreateAction extends BaseAction
{
    public function __invoke(PromoCodeRequest $request, PromoCodeService $promoCodeService)
    {
        $promoCodeService->create($request->payload());

        return $this->handleActionResult(
            route('admin.promo-code.list.page'),
            $request,
            ServiceActionResult::make(true, trans('admin.promo_code_created'))
        );
    }
}
