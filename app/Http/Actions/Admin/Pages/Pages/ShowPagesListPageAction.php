<?php

namespace App\Http\Actions\Admin\Pages\Pages;

use App\Http\Actions\Admin\BaseAction;


class ShowPagesListPageAction extends BaseAction
{
    public function __invoke()
    {
        return view('pages.admin.pages.list', []);
    }
}
