<?php

namespace App\Http\Actions\Admin\Colors;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Color\EditColorRequest;
use App\Models\Color;
use App\Services\Color\ColorService;

class ColorEditAction extends BaseAction
{
    public function __invoke(Color $color, EditColorRequest $request, ColorService $colorService)
    {
        $result = $colorService->editColor($color, $request->toDTO());

        return $this->handleActionResult(route('admin.color.list.page'), $request, $result);
    }
}
