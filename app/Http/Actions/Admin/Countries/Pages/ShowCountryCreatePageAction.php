<?php

namespace App\Http\Actions\Admin\Countries\Pages;

class ShowCountryCreatePageAction
{
    public function __invoke()
    {
        return view('pages.admin.countries.edit');
    }
}
