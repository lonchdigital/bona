<?php

namespace App\Http\Actions\Admin\Countries;

use App\Http\Actions\Admin\BaseAction;
use App\Models\Country;
use App\Services\Country\CountryService;
use Illuminate\Http\Request;

class CountryDeleteAction extends BaseAction
{
    public function __invoke(Country $country, Request $request, CountryService $countryService)
    {
        $result = $countryService->deleteCountry($country);

        return $this->handleActionResult(route('admin.country.list.page'), $request, $result);
    }
}
