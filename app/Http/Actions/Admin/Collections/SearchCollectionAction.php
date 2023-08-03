<?php

namespace App\Http\Actions\Admin\Collections;

use App\Http\Actions\Admin\BaseAction;
use App\Services\Collection\CollectionService;
use App\Http\Resources\Admin\Collection\CollectionResource;
use App\Http\Requests\Admin\Collection\SearchCollectionRequest;

class SearchCollectionAction extends BaseAction
{
    public function __invoke(SearchCollectionRequest $request, CollectionService $collectionService)
    {
        return CollectionResource::collection($collectionService->searchCollection($request->toDTO()));
    }
}
