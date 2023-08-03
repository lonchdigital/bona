<?php

namespace App\Http\Actions\Admin\Collections;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Collection;
use App\Services\Collection\CollectionService;
use Illuminate\Http\Request;

class CollectionDeleteAction extends BaseAction
{
    public function __invoke(Collection $collection, Request $request, CollectionService $service)
    {
        $result = $service->deleteCollection($collection);

        return $this->handleActionResult(route('admin.collection.list.page'), $request, $result);
    }
}
