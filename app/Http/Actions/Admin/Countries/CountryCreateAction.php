<?php

namespace App\Http\Actions\Admin\Countries;

use App\Http\Actions\Admin\BaseAction;
use App\Http\Requests\Admin\Country\CountryCreateRequest;
use App\Services\Country\CountryService;

class CountryCreateAction extends BaseAction
{
    public function __invoke(CountryCreateRequest $request, CountryService $countryService)
    {
        $creator = $this->getAuthUser();

        $result = $countryService->createCountry($creator, $request->toDTO());

        return $this->handleActionResult(route('admin.country.list.page'), $request, $result);
    }
}
