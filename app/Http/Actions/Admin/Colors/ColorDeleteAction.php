<?php

namespace App\Http\Actions\Admin\Colors;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Color;
use App\Services\Color\ColorService;
use Illuminate\Http\Request;

class ColorDeleteAction extends BaseAction
{
    public function __invoke(Color $color, Request $request, ColorService $colorService)
    {
        $result = $colorService->deleteColor($color);

        return $this->handleActionResult(route('admin.color.list.page'), $request, $result);
    }
}
