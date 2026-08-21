<?php

namespace App\Http\Actions\Admin\Authors\Pages;

use App\Models\Author;

class ShowAuthorEditPageAction
{
    public function __invoke(Author $author)
    {
        return view('pages.admin.authors.edit', [
            'author' => $author->load('certificates'),
        ]);
    }
}
