<?php

namespace App\Http\Actions\Admin\PromoCodes\Pages;

use App\Services\PromoCode\PromoCodeService;

class ShowPromoCodesListPageAction
{
    public function __invoke(PromoCodeService $promoCodeService)
    {
        return view('pages.admin.promo-codes.list', [
            'promoCodesPaginated' => $promoCodeService->paginate(),
        ]);
    }
}
