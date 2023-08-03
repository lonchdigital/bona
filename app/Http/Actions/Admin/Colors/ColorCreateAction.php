<?php

namespace App\Http\Actions\Admin\Colors;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Color\EditColorRequest;
use App\Services\Color\ColorService;

class ColorCreateAction extends BaseAction
{
    public function __invoke(EditColorRequest $request, ColorService $service)
    {
        $result = $service->createColor($request->toDTO());

        return $this->handleActionResult(route('admin.color.list.page'), $request, $result);
    }
}
