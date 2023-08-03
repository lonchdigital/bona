<?php

namespace App\Http\Actions\Admin\Brands\Pages;

class ShowBrandCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.brands.edit', [
            'isCreate' => true,
        ]);
    }
}
