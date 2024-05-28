<?php

namespace App\Http\Actions\Admin\Colors\Pages;

use App\Models\Color;
use App\Services\Color\ColorService;

class ShowColorEditPageAction
{
    public function __invoke(Color $color, ColorService $colorService)
    {
        return view('pages.admin.color.edit', [
            'color' => $color
        ]);
    }
}
