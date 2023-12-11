<?php

namespace App\Http\Actions\Admin\Works;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Work\CreateWorkRequest;
use App\Services\Work\WorkService;

class WorkCreateAction extends BaseAction
{
    public function __invoke(CreateWorkRequest $request, WorkService $service)
    {
        $result = $service->createWork($request->toDTO());

        return $this->handleActionResult(route('admin.work.list.page'), $request, $result);
    }
}
