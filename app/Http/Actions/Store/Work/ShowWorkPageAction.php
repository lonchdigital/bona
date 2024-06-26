<?php

namespace App\Http\Actions\Store\Work;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Work\WorkService;


class ShowWorkPageAction extends BaseAction
{
    public function __invoke(
        WorkService $workService,
    )
    {
        return view('pages.works.main', [
            'works' => $workService->getWorksPaginated(),
        ]);
    }
}
