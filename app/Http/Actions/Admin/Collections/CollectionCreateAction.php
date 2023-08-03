<?php

namespace App\Http\Actions\Admin\Collections;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Collection\CollectionCreateRequest;
use App\Services\Collection\CollectionService;

class CollectionCreateAction extends BaseAction
{
    public function __invoke(CollectionCreateRequest $request, CollectionService $service)
    {
        $creator = $this->getAuthUser();

        $result = $service->createCollection($creator, $request->toDTO());

        return $this->handleActionResult(route('admin.collection.list.page'), $request, $result);
    }
}
