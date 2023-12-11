<?php

namespace App\Http\Actions\Admin\Works;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Work\EditWorkRequest;
use App\Models\Work;
use App\Services\Work\WorkService;

class WorkEditAction extends BaseAction
{
    public function __invoke(Work $work, EditWorkRequest $request, WorkService $service)
    {
        $result = $service->updateWork($work, $request->toDTO());

        return $this->handleActionResult(route('admin.work.list.page'), $request, $result);
    }
}
