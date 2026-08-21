<?php

namespace App\Http\Actions\Admin\Authors;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Author\AuthorEditRequest;
use App\Models\Author;
use App\Services\Author\AuthorService;

class AuthorEditAction extends BaseAction
{
    public function __invoke(Author $author, AuthorEditRequest $request, AuthorService $service)
    {
        $result = $service->updateAuthor($author, $request->toDTO());

        return $this->handleActionResult(route('admin.author.list.page'), $request, $result);
    }
}
