<?php

namespace App\Http\Actions\Admin\Dashboard\Pages;

class ShowDashboardPageAction
{
    public function __invoke()
    {
        return view('pages.admin.dashboard.dashboard');
    }
}
