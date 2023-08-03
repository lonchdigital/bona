<?php

namespace App\Http\Actions\Admin\Collections;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Collection\CollectionEditRequest;
use App\Models\Collection;
use App\Services\Collection\CollectionService;

class CollectionEditAction extends BaseAction
{
    public function __invoke(Collection $collection, CollectionEditRequest $request, CollectionService $service)
    {
        $result = $service->editCollection($collection, $request->toDTO());

        return $this->handleActionResult(route('admin.collection.list.page'), $request, $result);
    }
}
