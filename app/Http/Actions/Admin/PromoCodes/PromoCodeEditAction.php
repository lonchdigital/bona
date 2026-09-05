<?php

namespace App\Http\Actions\Admin\PromoCodes;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\PromoCode\PromoCodeRequest;
use App\Models\PromoCode;
use App\Services\Base\ServiceActionResult;
use App\Services\PromoCode\PromoCodeService;

class PromoCodeEditAction extends BaseAction
{
    public function __invoke(PromoCode $promoCode, PromoCodeRequest $request, PromoCodeService $promoCodeService)
    {
        $promoCodeService->update($promoCode, $request->payload());

        return $this->handleActionResult(
            route('admin.promo-code.edit.page', $promoCode),
            $request,
            ServiceActionResult::make(true, trans('admin.promo_code_updated'))
        );
    }
}
