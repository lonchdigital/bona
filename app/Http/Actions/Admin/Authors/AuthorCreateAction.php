<?php

namespace App\Http\Actions\Admin\Authors;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Author\AuthorEditRequest;
use App\Services\Author\AuthorService;

class AuthorCreateAction extends BaseAction
{
    public function __invoke(AuthorEditRequest $request, AuthorService $service)
    {
        $result = $service->createAuthor($request->toDTO(), $request->user());

        return $this->handleActionResult(route('admin.author.list.page'), $request, $result);
    }
}
