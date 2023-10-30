<?php

namespace App\Http\Actions\Admin\ProductAttributes\Pages;

class ShowProductAttributeCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.product-attributes.edit');
    }
}
