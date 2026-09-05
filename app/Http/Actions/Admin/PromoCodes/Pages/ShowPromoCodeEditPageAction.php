<?php

namespace App\Http\Actions\Admin\PromoCodes\Pages;

use App\Models\Product;
use App\Models\PromoCode;

class ShowPromoCodeEditPageAction
{
    public function __invoke(PromoCode $promoCode)
    {
        return view('pages.admin.promo-codes.edit', [
            'promoCode' => $promoCode->load('products'),
            'products' => Product::query()->orderBy('id')->get(['id', 'name']),
        ]);
    }
}
