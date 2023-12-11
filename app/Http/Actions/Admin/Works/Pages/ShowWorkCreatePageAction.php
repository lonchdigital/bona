<?php

namespace App\Http\Actions\Admin\Works\Pages;

use App\Services\Admin\ProductField\ProductFieldService;
use App\Models\Work;

class ShowWorkCreatePageAction
{
    public function __invoke(ProductFieldService $service)
    {
        return view('pages.admin.works.edit', [

        ]);
    }
}
