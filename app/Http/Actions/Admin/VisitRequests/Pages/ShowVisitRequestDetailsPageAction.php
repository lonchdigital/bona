<?php

namespace App\Http\Actions\Admin\VisitRequests\Pages;

use App\Http\Actions\Admin\BaseAction;
use App\Models\VisitRequest;

class ShowVisitRequestDetailsPageAction extends BaseAction
{
    public function __invoke(VisitRequest $visitRequest)
    {
        return view('pages.admin.visit-requests.details', [
            'visitRequest' => $visitRequest,
        ]);
    }
}
