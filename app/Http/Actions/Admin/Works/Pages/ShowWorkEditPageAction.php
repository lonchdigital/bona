<?php

namespace App\Http\Actions\Admin\Works\Pages;

use App\Models\Work;

class ShowWorkEditPageAction
{
    public function __invoke(Work $work)
    {
        return view('pages.admin.works.edit', [
            'work' => $work,
        ]);
    }
}
