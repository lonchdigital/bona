<?php

namespace App\Http\Actions\Admin\Authors\Pages;

class ShowAuthorCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.authors.edit');
    }
}
