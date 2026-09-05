<?php

namespace App\Http\Actions\Admin\PromoCodes\Pages;

use App\Models\Product;

class ShowPromoCodeCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.promo-codes.edit', [
            'products' => Product::query()->orderBy('id')->get(['id', 'name']),
        ]);
    }
}
