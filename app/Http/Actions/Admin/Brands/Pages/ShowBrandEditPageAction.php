<?php

namespace App\Http\Actions\Admin\Brands\Pages;

use App\Models\Brand;

class ShowBrandEditPageAction
{
    public function __invoke(Brand $brand)
    {
        return view('pages.admin.brands.edit', [
            'isCreate' => false,
            'brand' => $brand,
        ]);
    }
}
