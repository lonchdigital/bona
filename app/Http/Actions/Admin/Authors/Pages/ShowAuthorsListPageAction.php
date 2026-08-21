<?php

namespace App\Http\Actions\Admin\Authors\Pages;

use App\Services\Author\AuthorService;

class ShowAuthorsListPageAction
{
    public function __invoke(AuthorService $service)
    {
        return view('pages.admin.authors.list', [
            'authorsPaginated' => $service->getAuthorsPaginated(),
        ]);
    }
}
