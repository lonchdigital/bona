<?php

namespace App\Http\Actions\Admin\StaticPages\Pages;

use App\Http\Actions\Admin\BaseAction;

class StaticPagesListPageAction extends BaseAction
{
    public function __invoke()
    {
        return view('pages.admin.static-pages.list');
    }
}
