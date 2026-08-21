<?php

namespace App\Http\Actions\Admin\Authors;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Author;
use App\Services\Author\AuthorService;
use Illuminate\Http\Request;

class AuthorDeleteAction extends BaseAction
{
    public function __invoke(Author $author, Request $request, AuthorService $service)
    {
        $result = $service->deleteAuthor($author);

        return $this->handleActionResult(route('admin.author.list.page'), $request, $result);
    }
}
