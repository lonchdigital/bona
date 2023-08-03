<?php

namespace App\Http\Actions\Admin\Colors\Pages;

use App\Services\Color\ColorService;

class ShowColorsListPageAction
{
    public function __invoke(ColorService $service)
    {
        return view('pages.admin.color.list', [
            'colorsPaginated' => $service->getColorsPaginated(),
        ]);
    }
}
