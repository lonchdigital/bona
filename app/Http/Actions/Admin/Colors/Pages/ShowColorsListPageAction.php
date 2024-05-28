<?php

namespace App\Http\Actions\Admin\Colors\Pages;

use App\Http\Requests\Admin\Color\ColorFilterRequest;
use App\Services\Color\ColorService;

class ShowColorsListPageAction
{
    public function __invoke(
        ColorFilterRequest $request,
        ColorService $service
    )
    {
        $dto = $request->toDTO();
        $colorsPaginated = $service->getColorsPaginated($dto);

        return view('pages.admin.color.list', [
            'colorsPaginated' => $colorsPaginated,
            'searchData' => $dto,
        ]);
    }
}
