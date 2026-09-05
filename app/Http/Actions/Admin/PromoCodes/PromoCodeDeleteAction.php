<?php

namespace App\Http\Actions\Admin\PromoCodes;

use App\Http\Actions\Admin\BaseAction;
use App\Models\PromoCode;
use App\Services\Base\ServiceActionResult;
use App\Services\PromoCode\PromoCodeService;
use Illuminate\Http\Request;

class PromoCodeDeleteAction extends BaseAction
{
    public function __invoke(PromoCode $promoCode, Request $request, PromoCodeService $promoCodeService)
    {
        $deleted = $promoCodeService->deleteOrDeactivate($promoCode);

        return $this->handleActionResult(
            route('admin.promo-code.list.page'),
            $request,
            ServiceActionResult::make(true, trans($deleted ? 'admin.promo_code_deleted' : 'admin.promo_code_deactivated'))
        );
    }
}
