<?php

namespace App\Http\Actions\Admin\Colors\Pages;


use App\Services\Color\ColorService;

class ShowColorCreatePageAction
{
    public function __invoke(ColorService $colorService)
    {
        return view('pages.admin.color.edit');
    }
}
