<?php

namespace App\Http\Actions\Admin\Works\Pages;

use App\Services\Admin\ProductField\ProductFieldService;

class ShowWorkCreatePageAction
{
    public function __invoke(ProductFieldService $service)
    {
        return view('pages.admin.works.edit', [

        ]);
    }
}
