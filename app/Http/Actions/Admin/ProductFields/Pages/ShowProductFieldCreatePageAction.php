<?php

namespace App\Http\Actions\Admin\ProductFields\Pages;

class ShowProductFieldCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.product-fields.edit');
    }
}
