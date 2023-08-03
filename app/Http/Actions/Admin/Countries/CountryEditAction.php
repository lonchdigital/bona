<?php

namespace App\Http\Actions\Admin\Countries;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Country\CountryEditRequest;
use App\Models\Country;
use App\Services\Country\CountryService;

class CountryEditAction extends BaseAction
{
    public function __invoke(Country $country, CountryEditRequest $request, CountryService $countryService)
    {
        $result = $countryService->editCountry($country, $request->toDTO());

        return $this->handleActionResult(route('admin.country.list.page'), $request, $result);
    }
}
