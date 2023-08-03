<?php

namespace App\Http\Actions\Admin\Countries\Pages;

use App\Services\Country\CountryService;

class ShowCountriesListPageAction
{
    public function __invoke(CountryService $countryService)
    {
        return view('pages.admin.countries.list', [
            'countriesPaginated' => $countryService->getCountriesPaginated(),
        ]);
    }
}
