<?php

namespace App\Http\Actions\Admin\Countries\Pages;

use App\Models\Country;

class ShowCountryEditPageAction
{
    public function __invoke(Country $country)
    {
        return view('pages.admin.countries.edit', [
            'country' => $country,
        ]);
    }
}
