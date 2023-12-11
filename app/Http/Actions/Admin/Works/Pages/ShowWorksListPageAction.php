<?php

namespace App\Http\Actions\Admin\Works\Pages;

use App\Services\Work\WorkService;

class ShowWorksListPageAction
{
    public function __invoke(WorkService $service)
    {
        $worksPaginated = $service->getWorksPaginated();

        return view('pages.admin.works.list', [
            'worksPaginated' => $worksPaginated,
        ]);
    }
}
