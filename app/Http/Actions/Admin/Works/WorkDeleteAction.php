<?php

namespace App\Http\Actions\Admin\Works;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Work;
use App\Services\Work\WorkService;
use Illuminate\Http\Request;

class WorkDeleteAction extends BaseAction
{
    public function __invoke(Work $work, Request $request, WorkService $service)
    {
        $result = $service->deleteWork($work);

        return $this->handleActionResult(route('admin.work.list.page'), $request, $result);
    }
}
